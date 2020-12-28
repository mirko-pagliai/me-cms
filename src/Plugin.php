<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 * @since       2.24.0
 */

namespace MeCms;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\MiddlewareQueue;
use MeCms\Command\AddUserCommand;
use MeCms\Command\GroupsCommand;
use MeCms\Command\Install\CopyConfigCommand;
use MeCms\Command\Install\CreateAdminCommand;
use MeCms\Command\Install\CreateGroupsCommand;
use MeCms\Command\Install\FixElFinderCommand;
use MeCms\Command\Install\RunAllCommand;
use MeCms\Command\UsersCommand;
use MeCms\Command\VersionUpdatesCommand;
use MeTools\Command\Install\CreateDirectoriesCommand;
use MeTools\Command\Install\CreateVendorsLinksCommand;
use MeTools\Command\Install\SetPermissionsCommand;

/**
 * Plugin class
 */
class Plugin extends BasePlugin
{
    /**
     * Returns `true` if is cli.
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
     * @uses isCli()
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        foreach ([
            'MeTools',
            'DatabaseBackup',
            'RecaptchaMailhide',
            'StopSpam',
            'Thumber\Cake',
            'Tokens',
        ] as $plugin) {
            $className = sprintf('%s\Plugin', $plugin);
            $plugin = new $className();
            $plugin->bootstrap($app);
            $plugin->disable('bootstrap');
            $app->addPlugin($plugin);
        }

        //Loads theme plugin
        $theme = getConfig('default.theme');
        if ($theme && !$app->getPlugins()->has($theme)) {
            $app->addPlugin($theme);
        }

        parent::bootstrap($app);

        if (!$this->isCli()) {
            //Loads DebugKit, if debugging is enabled
            if (getConfig('debug') && !$app->getPlugins()->has('DebugKit')) {
                try {
                    $app->addPlugin('DebugKit');
                // @codeCoverageIgnoreStart
                } catch (MissingPluginException $e) {
                    //Do not halt if the plugin is missing
                }
                // @codeCoverageIgnoreEnd
            }

            $app->addPlugin('WyriHaximus/MinifyHtml', ['path' => ROOT . DS . 'vendor' . DS . 'wyrihaximus' . DS . 'minify-html' . DS]);
        }
    }

    /**
     * Add console commands for the plugin
     * @param \Cake\Console\CommandCollection $commands The command collection to update
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('me_cms.add_user', AddUserCommand::class)
            ->add('me_cms.groups', GroupsCommand::class)
            ->add('me_cms.users', UsersCommand::class)
            ->add('me_cms.version_updates', VersionUpdatesCommand::class)
            ->add('me_cms.copy_config', CopyConfigCommand::class)
            ->add('me_cms.create_admin', CreateAdminCommand::class)
            ->add('me_cms.create_groups', CreateGroupsCommand::class)
            ->add('me_cms.fix_elfinder', FixElFinderCommand::class)
            ->add('me_cms.install', RunAllCommand::class);

        //Commands from MeTools
        return $commands->add('me_cms.create_directories', CreateDirectoriesCommand::class)
            ->add('me_cms.create_vendors_links', CreateVendorsLinksCommand::class)
            ->add('me_cms.set_permissions', SetPermissionsCommand::class);
    }

    /**
     * Adds middleware for the plugin
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to update
     * @return \Cake\Http\MiddlewareQueue
     * @since 2.26.4
     */
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        $key = Configure::read('Security.cookieKey', md5(Configure::read('Security.salt', '')));

        return $middleware->add(new EncryptedCookieMiddleware(['login'], $key));
    }
}
