<?php
/**
 * MeCmsBackendAppController
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

App::uses('MeToolsAppController', 'MeTools.Controller');
/**
 * Application level controller.
 */
class MeCmsBackendAppController extends MeToolsAppController {
	/**
	 * Components
	 * @var array
	 */
	public $components = array(
		'Auth' => array(
            'authenticate' => array('Form' => array(
				'contain'			=> array('Group' => array('id', 'name', 'level')),
				'passwordHasher'	=> 'Blowfish',
				'userModel'			=> 'MeCmsBackend.User'
			)),
			'authError'			=> 'You need to login first',
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
		'Form' => array('className' => 'MeTools.MeForm'),
		'Html' => array('className' => 'MeTools.MeHtml'),
		'MeTools.Library',
		'Paginator' => array('className' => 'MeTools.MePaginator')
	);
	
	/**
	 * Loads and gets the configuration.
	 * The file will be searched before in the APP (`app/Config`).
	 * If not available, it will be loaded by the plugin (`app/Plugin/MeCmsBackend/Config`)
	 * @return array Configuration
	 * @throws InternalErrorException
	 */
	private function _getConfig() {
		//Searches for the file in the APP `Config`
		if(is_readable(APP.($path = 'Config'.DS.'mecms_backend.php')))
			Configure::load('mecms_backend');
		//Searches for the file in the plugin `Config`
		elseif(is_readable(App::pluginPath('MeCmsBackend').$path))
			Configure::load('MeCmsBackend.mecms_backend');
		else
			throw new InternalErrorException(__d('me_cms_backend', 'The configuration file for the %s was not found', 'MeCms Backend'));
	
		return Configure::read('MeCmsBackend');
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 * @uses _getConfig() to load the configuration file
	 */
	public function beforeFilter() {
		//Loads and gets the configuration
		$this->config = $this->_getConfig();
		
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
		$this->set('config', $this->config);
		
		parent::beforeRender();
	}
}