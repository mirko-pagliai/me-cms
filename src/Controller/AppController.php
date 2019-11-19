<?php

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
use Cake\Event\Event;
use Cake\I18n\I18n;

/**
 * Application controller class
 */
abstract class AppController extends BaseAppController
{
    /**
     * Magic accessor for model autoloading.
     *
     * In addition to the method provided by CakePHP, it can also auto-load the
     *  associated tables.
     * @param string $name Property name
     * @return bool|object The model instance or false
     * @see \Cake\Controller\Controller::__get()
     * @since 2.27.1
     */
    public function __get($name)
    {
        list(, $class) = pluginSplit($this->modelClass, true);

        if ($class !== $name && $this->{$class}->hasAssociation($name)) {
            return $this->{$class}->getAssociation($name);
        }

        return parent::__get($name);
    }

    /**
     * Called before the controller action
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null
     * @uses isSpammer()
     */
    public function beforeFilter(Event $event)
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
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('default.records');

        $this->Auth->allow();

        //Layout for ajax and json requests
        if ($this->getRequest()->is(['ajax', 'json'])) {
            $this->viewBuilder()->setLayout('MeCms.ajax');
        }

        return parent::beforeFilter($event);
    }

    /**
     * Quick access to the `paging` request parameter
     * @return array
     * @since 2.27.1
     */
    public function getPaging()
    {
        return $this->getRequest()->getParam('paging', []);
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize()
    {
        //Loads components
        //The configuration for `AuthComponent`  takes place in the same class
        $this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
        $this->loadComponent('Recaptcha.Recaptcha', [
            'sitekey' => getConfigOrFail('Recaptcha.public'),
            'secret' => getConfigOrFail('Recaptcha.private'),
            'lang' => substr(I18n::getLocale(), 0, 2),
        ]);

        parent::initialize();
    }

    /**
     * Checks if the user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
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
    protected function isSpammer()
    {
        return $this->getRequest()->isSpammer() && !$this->getRequest()->isAction('ipNotAllowed', 'Systems');
    }
}
