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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller\Component
 */

App::uses('AppController', 'Controller');
App::uses('Plugin', 'MeTools.Utility');

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
		'Layout'	=> array('className' => 'MeTools.Layout'),
		'Library'	=> array('className' => 'MeTools.Library'),
		'Paginator'	=> array('className' => 'MeTools.MePaginator')
	);
	
	/**
	 * Loads all the plugin helpers for creating menus.
	 * @uses Plugin::getAll()
	 * @uses Plugin::getPath()
	 */
	protected function _loadMenus() {
		//Loads the `MenuHelper`
		$this->helpers['Menu'] = array('className' => 'MeCms.Menu');
			
		foreach(Plugin::getAll() as $plugin)
			if(is_readable(Plugin::getPath($plugin).'View'.DS.'Helper'.DS.$plugin.'MenuHelper.php')) {
				$helper = sprintf('%sMenu', $plugin);
				$this->helpers[$helper] = array('className' => sprintf('%s.%s', $plugin, $helper));
			}
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 * @throws InternalErrorException
	 * @uses MeToolsAppController::isAdminRequest()
	 * @uses _loadMenus()
	 * @uses isOffline()
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
		elseif($this->isOffline())
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
			'isMobile'	=> $this->request->isMobile(),
			'params'	=> $this->request->params,
			'query'		=> $this->request->query
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
	
	/**
	 * Checks if the site is offline
	 * @return bool TRUE if the site is offline, otherwise FALSE
	 * @uses MeToolsAppController::isAction()
	 * @uses MeToolsAppController::isRequestAction()
	 */
	public function isOffline() {
		return $this->config['offline'] && !$this->isAction(array('login', 'logout'), 'users') && !$this->isAction('offline', 'systems') && !$this->isRequestAction();
	}
	
	/**
	 * Uploads a file
	 * @param array $file File ($_FILE)
	 * @param string $target Target directory
	 */
	protected function upload($file, $target) {		
		//Checks if the file was successfully uploaded
		if(isset($file['error']) && $file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
			//Updated the target, adding the file name
			if(!file_exists($target.DS.$file['name']))
				$target = $target.DS.$file['name'];
			//If the file already exists, adds the name of the temporary file to the file name
			else
				$target = $target.DS.pathinfo($file['name'], PATHINFO_FILENAME).'_'.basename($file['tmp_name']).'.'.pathinfo($file['name'], PATHINFO_EXTENSION);

			//Checks if the file was successfully moved to the target directory
			if(!move_uploaded_file($file['tmp_name'], $file['target'] = $target))
				$this->set('error', __d('me_cms', 'The file was not successfully moved to the target directory'));
		}
		else
			$this->set('error', __d('me_cms', 'The file was not successfully uploaded'));

		$this->set(compact('file'));

		//Renders
		$this->render('Elements/backend/uploader/response', FALSE);
	}
}