<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 * @since       2.24.0
 */

namespace MeCms;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use MeCms\Command\Install\RunAllCommand;
use MeCms\Policy\ControllerResolver;
use MeTools\Command\Install\CreateDirectoriesCommand;
use MeTools\Command\Install\CreateVendorsLinksCommand;
use MeTools\Command\Install\SetPermissionsCommand;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Finder\Finder;

/**
 * Plugin class
 */
class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    /**
     * Returns `true` if is cli
     * @return bool
     */
    protected function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Load all the application configuration and bootstrap logic
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        $pluginsToLoad = ['Assets', 'MeTools', 'RecaptchaMailhide', 'StopSpam', 'Thumber/Cake', 'Tokens'];
        if (getConfig('default.theme')) {
            $pluginsToLoad[] = getConfig('default.theme');
        }
        if (!$this->isCli()) {
            $pluginsToLoad[] = 'WyriHaximus/MinifyHtml';
        }

        foreach ($pluginsToLoad as $plugin) {
            /** @var \Cake\Http\BaseApplication $app */
            $plugin = $app->getPlugins()->create($plugin);
            $plugin->bootstrap($app);
            $app->addPlugin($plugin);
        }

        $app->addPlugin('Authentication');
        $app->addPlugin('Authorization');

        parent::bootstrap($app);

        if (!$this->isCli()) {
            //Loads DebugKit, if debugging is enabled
            if (getConfig('debug') && !$app->getPlugins()->has('DebugKit')) {
                $app->addOptionalPlugin('DebugKit');
            }
        }
    }

    /**
     * Add console commands for the plugin
     * @param \Cake\Console\CommandCollection $commands The command collection to update
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        //Auto-discovers all `MeCms` commands.
        //Unlike the `CommandCollection::discoverPlugin()` method, it also finds installation commands
        $files = Finder::create()->files()->name('/Command\.php$/')->in($this->getPath() . 'src' . DS . 'Command');
        foreach ($files as $fileInfo) {
            $className = 'MeCms\\' . str_replace('/', '\\', substr($fileInfo->getPath(), strlen($this->getPath() . 'src' . DS))) . '\\' . $fileInfo->getBasename('.php');
            $name = Inflector::underscore(preg_replace('/Command\.php$/', '', $fileInfo->getFilename()));
            $commands->add('me_cms.' . $name, $className);
        }

        //Renames `RunAllCommand` command
        $commands->add('me_cms.install', RunAllCommand::class);

        //Adds commands from MeTools
        return $commands->add('me_cms.create_directories', CreateDirectoriesCommand::class)
            ->add('me_cms.create_vendors_links', CreateVendorsLinksCommand::class)
            ->add('me_cms.set_permissions', SetPermissionsCommand::class);
    }

    /**
     * Adds middleware for the plugin
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to update
     * @return \Cake\Http\MiddlewareQueue
     * @since 2.26.4
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $key = Configure::read('Security.cookieKey', md5(Configure::read('Security.salt', '')));

        return $middlewareQueue->add(new EncryptedCookieMiddleware(['login'], $key))
            ->add(new AuthenticationMiddleware($this))
            ->add(new AuthorizationMiddleware($this, ['requireAuthorizationCheck' => false]));
    }

    /**
     * Returns a service provider instance
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     * @see \MeCms\Controller\UsersController::login()
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $fields = ['username' => 'username', 'password' => 'password'];
        $loginUrl = Router::url(['_name' => 'login']);

        $service = new AuthenticationService();
        $service->setConfig(['unauthenticatedRedirect' => $loginUrl, 'queryParam' => 'redirect']);

        //Loads the authenticators. Session should be first
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', compact('fields', 'loginUrl'));
        $service->loadAuthenticator('Authentication.Cookie', compact('fields', 'loginUrl') + ['cookie' => ['expires' => '+1 year']]);

        //Loads identifiers
        $service->loadIdentifier('Authentication.Password', [
            'fields' => ['username' => ['username', 'email'], 'password' => 'password'],
            'resolver' => ['className' => 'Authentication.Orm', 'userModel' => 'MeCms.Users', 'finder' => 'auth'],
        ]);

        return $service;
    }

    /**
     * Returns `AuthorizationServiceInterface` instance
     * @param \Psr\Http\Message\ServerRequestInterface $request Server request
     * @return \Authorization\AuthorizationServiceInterface
     * @throws \RuntimeException When authorization method has not been defined
     */
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        return new AuthorizationService(new ControllerResolver());
    }
}
