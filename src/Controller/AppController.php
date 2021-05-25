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
 */

namespace MeCms\Controller;

use App\Controller\AppController as BaseAppController;
use Cake\Event\EventInterface;
use Cake\I18n\I18n;
use RuntimeException;

/**
 * Application controller class
 * @property \MeCms\Controller\Component\AuthComponent $Auth
 * @property \MeTools\Controller\Component\FlashComponent $Flash
 */
abstract class AppController extends BaseAppController
{
    /**
     * Magic accessor for model autoloading.
     *
     * In addition to the method provided by CakePHP, it can also auto-load the
     *  associated tables.
     * @param string $name Property name
     * @return \Cake\Datasource\RepositoryInterface|null The model instance or null
     * @see \Cake\Controller\Controller::__get()
     * @since 2.27.1
     */
    public function __get(string $name)
    {
        [, $class] = pluginSplit($this->modelClass, true);

        if ($class !== $name && $this->{$class}->hasAssociation($name)) {
            return $this->{$class}->getAssociation($name);
        }

        return parent::__get($name);
    }

    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event EventInterface
     * @return \Cake\Http\Response|null|void
     * @uses isSpammer()
     */
    public function beforeFilter(EventInterface $event)
    {
        //Checks if the site is offline
        if ($this->getRequest()->isOffline()) {
            return $this->redirect(['_name' => 'offline']);
        }

        //Checks if the user's IP address is reported as spammer
        if ($this->isSpammer()) {
            return $this->redirect(['_name' => 'ipNotAllowed']);
        }

        $this->viewBuilder()->setClassName('MeCms.View/App');

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/4.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('default.records');

        $this->Auth->allow();

        //Layout for ajax and json requests
        if ($this->getRequest()->is(['ajax', 'json'])) {
            $this->viewBuilder()->setLayout('MeCms.ajax');
        }

        return parent::beforeFilter($event);
    }

    /**
     * Gets the the `paging` request attribute and parameter
     * @return array
     * @since 2.27.1
     */
    public function getPaging()
    {
        return $this->getRequest()->getAttribute('paging') ?? $this->getRequest()->getParam('paging', []);
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        //Loads components
        //The configuration for `AuthComponent`  takes place in the same class
        $this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);

        //Loads Recaptcha. Throws an exception if the keys are not set or are the default ones
        if (getConfig('MeCms.security.recaptcha')) {
            try {
                [$sitekey, $secret] = array_values(getConfigOrFail('Recaptcha'));
                if ($sitekey == 'your-public-key-here' || $secret == 'your-public-key-here') {
                    throw new RuntimeException();
                }
            } catch (RuntimeException $e) {
                throw new RuntimeException('Missing Recaptcha keys. You can rename the `config/recaptcha.example.php` file as `recaptcha.php` and change the keys');
            }

            $this->loadComponent('Recaptcha.Recaptcha', compact('sitekey', 'secret') + ['lang' => substr(I18n::getLocale(), 0, 2)]);
        }

        parent::initialize();
    }

    /**
     * Checks if the user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null): bool
    {
        //Only admin and managers can access admin actions
        //Any registered user can access actions without prefix. Default deny
        return !$this->getRequest()->getParam('prefix') || $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Checks if the user's IP address is reported as a spammer
     * @return bool
     * @since 2.15.2
     */
    protected function isSpammer(): bool
    {
        return $this->getRequest()->isSpammer() && !$this->getRequest()->isAction('ipNotAllowed', 'Systems');
    }

    /**
     * Sets the `paging` request attribute and parameter
     * @param array $paging Paging value
     * @return $this
     * @since 2.29.1
     */
    public function setPaging(array $paging)
    {
        $request = $this->getRequest()->withAttribute('paging', $paging)->withParam('paging', $paging);

        return $this->setRequest($request);
    }
}
