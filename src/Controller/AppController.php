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

use App\Controller\AppController as BaseController;
use Cake\Event\Event;
use Cake\I18n\I18n;

/**
 * Application controller class
 */
class AppController extends BaseController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @see http://api.cakephp.org/3.4/class-Cake.Controller.Controller.html#_beforeFilter
     * @uses App\Controller\AppController::beforeFilter()
     * @uses isBanned()
     * @uses isOffline()
     */
    public function beforeFilter(Event $event)
    {
        //Checks if the site is offline
        if ($this->isOffline()) {
            return $this->redirect(['_name' => 'offline']);
        }

        //Checks if the user's IP address is banned
        if ($this->isBanned()) {
            return $this->redirect(['_name' => 'ipNotAllowed']);
        }

        $this->viewBuilder()->setClassName('MeCms.View/App');

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = getConfigOrFail('default.records');

        if ($this->request->isAdmin()) {
            $this->viewBuilder()->setClassName('MeCms.View/Admin');

            $this->paginate['limit'] = getConfigOrFail('admin.records');
        } else {
            //Authorizes the current action
            $this->Auth->allow();
        }

        $this->paginate['maxLimit'] = $this->paginate['limit'];

        //Layout for ajax and json requests
        if ($this->request->is(['ajax', 'json'])) {
            $this->viewBuilder()->setLayout('MeCms.ajax');
        }

        parent::beforeFilter($event);
    }

    /**
     * Called after the controller action is run, but before the view is
     *  rendered.
     * You can use this method to perform logic or set view variables that are
     *  required on every request.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @see http://api.cakephp.org/3.4/class-Cake.Controller.Controller.html#_beforeRender
     * @uses App\Controller\AppController::beforeRender()
     */
    public function beforeRender(Event $event)
    {
        //Loads the `Auth` helper.
        //The `helper is loaded here (instead of the view) to pass user data
        $this->viewBuilder()->setHelpers(['MeCms.Auth' => $this->Auth->user()]);

        parent::beforeRender($event);
    }

    /**
     * Initialization hook method
     * @return void
     * @uses App\Controller\AppController::initialize()
     */
    public function initialize()
    {
        //Loads components
        //The configuration for `AuthComponent`  takes place in the same class
        $this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
        $this->loadComponent('MeTools.Uploader');
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
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Any registered user can access public functions
        if (!$this->request->getParam('prefix')) {
            return true;
        }

        //Only admin and managers can access all admin actions
        if ($this->request->isAdmin()) {
            return $this->Auth->isGroup(['admin', 'manager']);
        }

        //Default deny
        return false;
    }

    /**
     * Checks if the user's IP address is banned
     * @return bool
     * @since 2.15.2
     */
    protected function isBanned()
    {
        return $this->request->isBanned() && !$this->request->isAction('ipNotAllowed', 'Systems');
    }

    /**
     * Checks if the site is offline
     * @return bool
     * @since 2.15.2
     */
    protected function isOffline()
    {
        return $this->request->isOffline();
    }

    /**
     * Internal method to set an upload error.
     *
     * It saves the error as view var that `JsonView` should serialize and sets
     *  the response status code to 500.
     * @param string $error Error message
     * @return void
     * @since 2.18.1
     */
    protected function setUploadError($error)
    {
        $this->response = $this->response->withStatus(500);

        $this->set(compact('error'));
        $this->set('_serialize', ['error']);
    }
}
