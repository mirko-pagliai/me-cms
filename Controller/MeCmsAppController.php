<?php
/**
 * MeAuthComponent
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller\Component
 */

App::uses('AppController', 'Controller');

/**
 * Application level controller.
 */
class MeCmsAppController extends AppController {
	/**
	 * Components
	 * @var array
	 */
	public $components = array(
		'Auth' => array(
            'authenticate' => array('Form' => array(
				'contain'			=> array('Group' => array('id', 'name')),
				'passwordHasher'	=> 'Blowfish',
				'userModel'			=> 'MeCms.User'
			)),
			'authorize'			=> array('Controller'),
			'className'			=> 'MeCms.MeAuth',
			'loginAction'		=> array('controller' => 'users', 'action' => 'login', 'admin' => FALSE, 'plugin' => 'me_cms'),
            'loginRedirect'		=> '/admin',
            'logoutRedirect'	=> '/login'
        ),
		'Config'	=> array('className' => 'MeCms.Config'),
		'RequestHandler',
		'Session'	=> array('className' => 'MeTools.MeSession')
	);
	
	/**
	 * Configuration.
	 * It will be set by `ConfigComponent`.
	 * @var array
	 */
	protected $config = array();

	/**
	 * Helpers
	 * @var array 
	 */
	public $helpers = array(
		'Auth'		=> array('className' => 'MeCms.Auth'),
		'Dropdown'	=> array('className' => 'MeTools.Dropdown'),
		'Form'		=> array('className' => 'MeTools.MeForm'),
		'Html'		=> array('className' => 'MeTools.MeHtml'),
		'Layout'	=> array('className' => 'MeCms.Layout'),
		'Library'	=> array('className' => 'MeTools.Library'),
		'Paginator'	=> array('className' => 'MeTools.MePaginator')
	);
	
	/**
	 * Loads all the plugin helpers for creating menus.
	 */
	protected function _loadMenus() {
		//Loads the `MenuHelper`
		$this->helpers['Menu'] = array('className' => 'MeCms.Menu');
			
		foreach(CakePlugin::loaded() as $plugin)
			if(is_readable(CakePlugin::path($plugin).'View'.DS.'Helper'.DS.$plugin.'MenuHelper.php')) {
				$helper = sprintf('%sMenu', $plugin);
				$this->helpers[$helper] = array('className' => sprintf('%s.%s', $plugin, $helper));
			}
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 * @throws InternalErrorException
	 * @uses _loadMenus()
	 * @uses isAdminRequest()
	 */
	public function beforeFilter() {		
		if(!empty($this->Auth)) {
			//Sets the authenticaton message error
			$this->Auth->authError = __d('me_cms', 'You need to login first');
			//Sets the element that will be used for flash auth errors
			//http://stackoverflow.com/a/20545037/1480263
			$this->Auth->flash['element'] = 'MeTools.error';
		}
		
		//Authorizes the current action, if it's not an admin request
		if(!$this->isAdminRequest())
			$this->Auth->allow($this->action);
		
		//Sets the theme
		if(!empty($this->config['theme'])) {
			//Checks if the theme exists
			if(!is_readable(App::themePath($this->config['theme'])))
				throw new InternalErrorException(__d('me_cms', 'The theme %s was not found', $this->config['theme']));

			$this->theme = $this->config['theme'];
		}
		
		//If this is an admin request
		if($this->isAdminRequest()) {
			//Loads all the plugin helpers for creating menus.
			$this->_loadMenus();
			//Sets the layout
			$this->layout = 'MeCms.backend';
		}
		//Else, if the site has been taken offline
		elseif($this->config['offline'] && !$this->isAction('login', 'users') && !$this->isAction('logout', 'users') && !$this->isAction('offline', 'systems') && !$this->isRequestAction())
			$this->redirect(array('controller' => 'systems', 'action' => 'offline', 'plugin' => 'me_cms'));
		//Else, if this is not an admin request and the site is online
		else {
			//Loads the `WidgetHelper`
			$this->helpers['Widget'] = array('className' => 'MeCms.Widget');
			
			//Sets the layout
			$this->layout = 'MeCms.frontend';
		}
		
		parent::beforeFilter();
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered. 
	 * It's used to perform logic or set view variables that are required on every request.
	 */
	public function beforeRender() {
		//Sets the user authentication data and the `isMobile` var
		$this->set(array(
			'auth'		=> empty($this->Auth) ? FALSE : $this->Auth->user(),
			'isMobile'	=> $this->request->isMobile()
		));
		
		parent::beforeRender();
	}
	
	/**
	 * Checks if the specified action is the current one.
	 * 
	 * Optionally, it can also check the controller.
	 * @param string $action Action name
	 * @param string $controller Controller name
	 * @return bool TRUE if it's the current one, otherwise FALSE
	 * @uses isController()
	 */
	public function isAction($action, $controller = NULL) {
		$action = $this->request->params['action'] === $action;
		
		if(empty($controller))
			return $action;
		
		return $action && $this->isController($controller);
	}
	
	/**
	 * Checks if this is an admin request
	 * @return boolean TRUE if is an admin request, otherwise FALSE
	 */
	public function isAdminRequest() {
		return !empty($this->request->params['admin']);
	}
	
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isManager()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can access every action
		return $this->Auth->isManager();
	}
	
	/**
	 * Checks if the specified controller is the current one
	 * @param string $controller Controller name
	 * @return bool TRUE if it's the current one, otherwise FALSE
	 */
	public function isController($controller) {
		return $this->request->params['controller'] === $controller;
	}
	
	/**
	 * Checks if the current action is a "request action"
	 * @return bool TRUE if it's a "request action", otherwise FALSE
	 */
	public function isRequestAction() {
		return !empty($this->request->params['requested']);
	}
}