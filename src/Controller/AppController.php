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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use App\Controller\AppController as BaseController;
use Cake\Network\Exception\InternalErrorException;

/**
 * Application controller class
 */
class AppController extends BaseController {
	/**
     * Checks if the latest search has been executed out of the minimum interval
     * @param string $query_id Query
	 * @return bool
	 */
	protected function _checkLastSearch($query_id = FALSE) {
        $interval = config('security.search_interval');
		
        if(!$interval) {
            return TRUE;
        }
        
        if($query_id) {
            $query_id = md5($query_id);
        }
		
		$last_search = $this->request->session()->read('last_search');
		
		if($last_search) {
			//Checks if it's the same search
			if($query_id && !empty($last_search['id']) && $query_id === $last_search['id']) {
				return TRUE;
            }
			//Checks if the interval has not yet expired
			elseif(($last_search['time'] + $interval) > time()) {
				return FALSE;
            }
		}
		
		$this->request->session()->write('last_search', [
            'id' => $query_id,
            'time' => time(),
        ]);
		
		return TRUE;
	}
    
    /**
     * Internal method to download a file
     * @param string $path File path
     * @param bool $force If `TRUE`, it forces the download
     * @throws InternalErrorException
     */
    protected function _download($path, $force = TRUE) {
        if(!is_readable($path)) {
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($path)));
        }
                
		$this->response->file($path, ['download' => !empty($force)]);
		return $this->response;
    }
    
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before 
     *  each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.3/class-Cake.Controller.Controller.html#_beforeFilter
	 * @uses App\Controller\AppController::beforeFilter()
	 * @uses isOffline()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		//Checks if the site is offline
		if($this->isOffline()) {
			return $this->redirect(['_name' => 'offline']);
        }
		
		//Checks if the user's IP address is banned
		if($this->request->isBanned() && !$this->request->is('action', 'ip_not_allowed', 'Systems')) {
			return $this->redirect(['_name' => 'ip_not_allowed']);
        }
		
		//Authorizes the current action, if this is not an admin request
		if(!$this->request->isAdmin()) {
			$this->Auth->allow($this->request->action);
        }
		
		//Sets the paginate limit and the maximum paginate limit
		//See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if($this->request->isAdmin()) {
            $this->paginate['limit'] = config('admin.records');
        }
        else {
            $this->paginate['limit'] = config('default.records');
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
	 * @see http://api.cakephp.org/3.3/class-Cake.Controller.Controller.html#_beforeRender
	 * @uses App\Controller\AppController::beforeRender()
	 */
	public function beforeRender(\Cake\Event\Event $event) {
		//Layout for ajax requests
		if($this->request->is('ajax')) {
			$this->viewBuilder()->layout('MeCms.ajax');
        }
		
		//Uses a custom View class (`MeCms.AppView` or `MeCms.AdminView`)
        if($this->request->isAdmin()) {
            $this->viewBuilder()->className('MeCms.View/Admin');
        }
        else {
            $this->viewBuilder()->className('MeCms.View/App');
        }
        
        //Loads the `Auth` helper.
        //The `helper is loaded here (instead of the view) to pass user data
        $this->viewBuilder()->helpers(['MeCms.Auth' => $this->Auth->user()]);
		
		parent::beforeRender($event);
	}
	
	/**
	 * Initialization hook method
	 * @uses App\Controller\AppController::initialize()
	 */
	public function initialize() {
		//Loads components
        //The configuration for `AuthComponent`  takes place in the same class
		$this->loadComponent('Cookie');
		$this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('MeTools.Uploader');
		
		if(config('security.recaptcha')) {
			$this->loadComponent('MeTools.Recaptcha');
        }
		
		parent::initialize();
    }
	
	/**
	 * Checks if the user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the 
     *  user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {		
		//By default, admins and managers can access all actions
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
	 * Checks if the site is offline
	 * @return bool
	 */
	protected function isOffline() {
		if(!config('default.offline')) {
			return FALSE;
        }
		
		//Always online for admin requests
		if($this->request->isAdmin()) {
			return FALSE;
        }
		
		//Always online for these actions
		if($this->request->is('action', ['offline', 'login', 'logout'])) {
			return FALSE;
        }
		
		return TRUE;
	}
}