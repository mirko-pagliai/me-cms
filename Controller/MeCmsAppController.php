<?php
App::uses('MeToolsAppController', 'MeTools.Controller');

/**
 * MeCmsAppController
 *
 * This file is part of MeCms Backend
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Controller
 */

/**
 * Application level controller.
 */
class MeCmsAppController extends MeToolsAppController {
	/**
	 * Components
	 * @var array
	 */
	public $components = array(
		'Auth' => array(
            'authenticate' => array('Form' => array(
				'contain'			=> array('Group' => array('id', 'name', 'level')),
				'passwordHasher'	=> 'Blowfish',
				'userModel'			=> 'MeCms.User'
			)),
			'authError'			=> 'You need to login first',
			'loginAction'		=> array('controller' => 'users', 'action' => 'login', 'admin' => FALSE),
            'loginRedirect'		=> '/admin',
            'logoutRedirect'	=> '/login'
        ),
		'Session' => array('className' => 'MeTools.MeSession')
	);
	
	/**
	 * Helpers
	 * @var array 
	 */
	public $helpers = array(
		'MeTools.Library',
		'Paginator' => array('className' => 'MeTools.MePaginator')
	);
	
	/**
	 * Loads the configuration file
	 * The file will be searched before in the APP (`app/Config`).
	 * If not available, it will be loaded by the plugin (`app/Plugin/MeCms/Config`)
	 * @throws InternalErrorException
	 */
	private function _loadConfig() {
		//Searches for the file in the APP `Config`
		if(is_readable(APP.($path = 'Config'.DS.'mecms.php')))
			Configure::load('mecms');
		//Searches for the file in the plugin `Config`
		elseif(is_readable(App::pluginPath('MeCms').$path))
			Configure::load('MeCms.mecms');
		else
			throw new InternalErrorException(__d('me_cms', 'The configuration file for %s was not found', 'MeCms'));
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 * @uses _loadConfig() to load the configuration file
	 */
	public function beforeFilter() {
		//Loads configuration file
		$this->_loadConfig();
		
		parent::beforeFilter();
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered. 
	 * It's used to perform logic or set view variables that are required on every request.
	 */
	public function beforeRender() {
		//Sets the admin layout
		if(!empty($this->request->params['admin']))
			$this->layout = 'admin';
		
		//Sets the configuration of MeCms for the view
		$this->set('config', Configure::read('MeCms'));
		
		parent::beforeRender();
	}
}