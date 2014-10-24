<?php
/**
 * MeCmsAppController
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
 * @package		MeCms\Controller
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
			'loginAction'		=> array('controller' => 'users', 'action' => 'login', 'admin' => FALSE),
            'loginRedirect'		=> '/admin',
            'logoutRedirect'	=> '/login'
        ),
		'Session' => array('className' => 'MeTools.MeSession')
	);
	
	/**
	 * Configuration
	 * @var array
	 */
	protected $config = array();

	/**
	 * Helpers
	 * @var array 
	 */
	public $helpers = array(
		'Dropdown'	=> array('className' => 'MeTools.Dropdown'),
		'Form'		=> array('className' => 'MeTools.MeForm'),
		'Html'		=> array('className' => 'MeTools.MeHtml'),
		'Library'	=> array('className' => 'MeTools.Library'),
		'Paginator'	=> array('className' => 'MeTools.MePaginator')
	);
	
	/**
	 * Loads and gets the configuration.
	 * The file will be searched before in the APP (`app/Config`).
	 * If not available, it will be loaded by the plugin (`app/Plugin/MeCms/Config`)
	 * @return array Configuration
	 * @throws InternalErrorException
	 * @uses isAdminRequest()
	 */
	private function _getConfig() {
		//Searches for the file in the APP `Config`
		if(is_readable(APP.($path = 'Config'.DS.'mecms.php')))
			Configure::load('mecms');
		//Searches for the file in the plugin `Config`
		elseif(is_readable(App::pluginPath('MeCms').$path))
			Configure::load('MeCms.mecms');
		else
			throw new InternalErrorException(__d('me_cms', 'The configuration file for %s was not found', 'MeCms'));
	
		//If it's an admin request
		if($this->isAdminRequest())
			return am(Configure::read('backend'), Configure::read('general'));
		
		return am(Configure::read('frontend'), Configure::read('general'));
	}
	
	/**
	 * Checks if this is an admin request
	 * @return boolean TRUE if is an admin request, otherwise FALSE
	 */
	protected function isAdminRequest() {
		return !empty($this->request->params['admin']);
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 * @throws InternalErrorException
	 * @uses _getConfig() to load the configuration file
	 * @uses isAdminRequest()
	 */
	public function beforeFilter() {
		//Loads and gets the configuration
		$this->config = $this->_getConfig();
		
		//Sets debug and cache
		Configure::write('debug', $this->config['debug'] ? 2 : 0);
		Configure::write('Cache.disable', !$this->config['cache']);
		
		//Sets the authenticaton message error
		$this->Auth->authError = __d('me_cms', 'You need to login first');
		//Sets the element that will be used for flash auth errors
		//http://stackoverflow.com/a/20545037/1480263
		if(!empty($this->Auth))
			$this->Auth->flash['element'] = 'MeTools.error';
		
		//Sets the session timeout
		Configure::write('Session.timeout', $this->config['timeout']);
		
		//If it's not an admin request, authorizes the current action
		if(!$this->isAdminRequest())
			$this->Auth->allow($this->action);
		
		//Sets the theme
		if(!empty($this->config['theme'])) {
			//Checks if the theme exists
			if(!is_readable(App::themePath($theme = $this->config['theme'])))
				throw new InternalErrorException(__d('me_cms', 'The theme %s was not found', $theme));

			$this->theme = $theme;
		}
			
		//Sets the layout
		$this->layout = $this->isAdminRequest() ? 'backend' : 'frontend';
		
		parent::beforeFilter();
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered. 
	 * It's used to perform logic or set view variables that are required on every request.
	 */
	public function beforeRender() {
		//Sets the user authentication data, the configuration array and the 'isMobile' var
		$this->set(array(
			'auth'		=> empty($this->Auth) ? FALSE : $this->Auth->user(),
			'config'	=> $this->config,
			'isMobile'	=> $this->request->isMobile()
		));
		
		parent::beforeRender();
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
}