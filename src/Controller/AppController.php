<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use App\Controller\AppController as BaseController;
use Cake\Event\Event;

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

        //Authorizes the current action, if this is not an admin request
        if (!$this->request->isAdmin()) {
            $this->Auth->allow($this->request->action);
        }

        //Adds the current sort field in the whitelist of pagination
        if ($this->request->isAdmin() && $this->request->query('sort')) {
            $this->paginate['sortWhitelist'] = [$this->request->query('sort')];
        }

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = config('default.records');

        if ($this->request->isAdmin()) {
            $this->paginate['limit'] = config('admin.records');
        }

        $this->paginate['maxLimit'] = $this->paginate['limit'];

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
        //Layout for ajax requests
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->layout('MeCms.ajax');
        }

        $this->viewBuilder()->className('MeCms.View/App');

        //Uses a custom View class (`MeCms.AppView` or `MeCms.AdminView`)
        if ($this->request->isAdmin()) {
            $this->viewBuilder()->className('MeCms.View/Admin');
        }

        //Loads the `Auth` helper.
        //The `helper is loaded here (instead of the view) to pass user data
        $this->viewBuilder()->helpers(['MeCms.Auth' => $this->Auth->user()]);

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
        $this->loadComponent('Cookie');
        $this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('MeTools.Uploader');

        if (config('security.recaptcha')) {
            $this->loadComponent('MeTools.Recaptcha');
        }

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
        //By default, admins and managers can access all actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Checks if the user's IP address is banned
     * @return bool
     * @since 2.15.2
     */
    public function isBanned()
    {
        return $this->request->isBanned() && !$this->request->isAction('ipNotAllowed', 'Systems');
    }

    /**
     * Checks if the site is offline
     * @return bool
     * @since 2.15.2
     */
    public function isOffline()
    {
        return $this->request->isOffline();
    }
}
