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
use Cake\I18n\I18n;

/**
 * Application controller class
 */
class AppController extends BaseController {
	/**
	 * Gets the user's language
	 * @return mixed Language code or FALSE
	 * @throws \Cake\Network\Exception\InternalErrorException
	 * @uses MeTools\Core\Plugin::path()
	 */
	protected function _getLanguage() {
		$config = config('main.language');
		$language = $this->request->env('HTTP_ACCEPT_LANGUAGE');
		$path = \MeTools\Core\Plugin::path('MeCms', 'src'.DS.'Locale');
		
		if(empty($config) || $config === 'auto') {
			if(is_readable($path.DS.substr($language, 0, 5).DS.'me_cms.po'))
				return substr($language, 0, 5);
			elseif(is_readable($path.DS.substr($language, 0, 2).DS.'me_cms.po'))
				return substr($language, 0, 2);
		}
		elseif(!empty($config)) {
            $file = $path.DS.$config.DS.'me_cms.po';
            
			if(!is_readable($file))
				throw new \Cake\Network\Exception\InternalErrorException(__d('me_tools', 'File or directory {0} not readable', $file));
			
			return $config;
		}
		
		return FALSE;
	}
	
	/**
	 * Internal method to uploads a file
	 * @param array $file File ($_FILE)
	 * @param string $target Target directory
	 * @return mixed Full file path or FALSE if there's an error
	 */
	protected function _upload($file, $target) {
		//Checks if the file was successfully uploaded
		if(isset($file['error']) && $file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
			//Updated the target, adding the filename
			if(!file_exists($target.DS.$file['name']))
				$target .= DS.$file['name'];
			//If the file already exists, adds the name of the temporary file to the filename
			else
				$target .= DS.pathinfo($file['name'], PATHINFO_FILENAME).'_'.basename($file['tmp_name']).'.'.pathinfo($file['name'], PATHINFO_EXTENSION);

			//Checks if the file was successfully moved to the target directory
			if(!@move_uploaded_file($file['tmp_name'], $file['target'] = $target))
				$error = __d('me_cms', 'The file was not successfully moved to the target directory');
		}
		else
			$error = __d('me_cms', 'The file was not successfully uploaded');
		
		$this->set(am(['error' => empty($error) ? FALSE : $error], compact('file')));
		
		return empty($error) ? $target : FALSE;
	}
	
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.2/class-Cake.Controller.Controller.html#_beforeFilter
	 * @uses App\Controller\AppController::beforeFilter()
	 * @uses MeTools\Network\Request::isAction()
	 * @uses _getLanguage()
	 * @uses isBanned()
	 * @uses isOffline()
	 * @uses setLanguage()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		//Checks if the site is offline
		if($this->isOffline())
			return $this->redirect(['_name' => 'offline']);
		
		//Checks if the user's IP address is banned
		if(!$this->request->isAction('ip_not_allowed', 'Systems') && $this->isBanned())
			return $this->redirect(['_name' => 'ip_not_allowed']);
		
		//Sets the user's language
		I18n::locale($this->_getLanguage());
		
		//If the current request has no prefix, it authorizes the current action
		if(!$this->request->param('prefix'))
			$this->Auth->allow($this->request->action);
		
		if(!$this->Auth->user())
			$this->Auth->config('authError', FALSE);
		
		//Sets the paginate limit and the maximum paginate limit
		//See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
		$this->paginate['limit'] = $this->paginate['maxLimit'] = $this->request->isAdmin() ? config('backend.records') : config('frontend.records');
		
		parent::beforeFilter($event);
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered.
	 * You can use this method to perform logic or set view variables that are required on every request.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.2/class-Cake.Controller.Controller.html#_beforeRender
	 * @uses App\Controller\AppController::beforeRender()
	 */
	public function beforeRender(\Cake\Event\Event $event) {
		//Ajax layout
		if($this->request->is('ajax'))
			$this->viewBuilder()->layout('MeCms.ajax');
		
		//Uses a custom View class (`MeCms.AppView` or `MeCms.AdminView`)
		$this->viewClass = !$this->request->isAdmin() ? 'MeCms.View/App' : 'MeCms.View/Admin';
		
		//Sets auth data for views
		$this->set('auth', empty($this->Auth) ? FALSE : $this->Auth->user());
		
		parent::beforeRender($event);
	}
	
	/**
	 * Initialization hook method
	 * @uses App\Controller\AppController::initialize()
	 */
	public function initialize() {
		//Loads components
		$this->loadComponent('Cookie');
		$this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler');
		$this->loadComponent('MeCms.Security');
		
		if(config('security.recaptcha'))
			$this->loadComponent('MeTools.Recaptcha');
		
		parent::initialize();
    }
	
	/**
	 * Checks if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {		
		//By default, admins and managers can access every action
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
	 * Checks if the user's IP address is banned
	 * @return bool
	 * @uses MeCms\Controller\Component\SecurityComponent::isBanned()
	 */
	protected function isBanned() {
		if(!config('security.banned_ip'))
			return FALSE;
		
		$banned_ip = is_string(config('security.banned_ip')) ? [config('security.banned_ip')] : config('security.banned_ip');
		
		return $this->Security->isBanned($banned_ip);
	}
	
	/**
	 * Checks if the site is offline
	 * @return bool
	 * @uses MeTools\Network\Request::isAction()
	 */
	protected function isOffline() {
		if(!config('frontend.offline'))
			return FALSE;
		
		//Always online for these actions
		if($this->request->isAction(['offline', 'login', 'logout']))
			return FALSE;
		
		//Always online for admin requests
		if($this->request->isAdmin())
			return FALSE;
		
		return TRUE;
	}
}