<?php
/**
 * ConfigComponent
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

App::uses('Component', 'Controller');

/**
 * It automatically handles the configuration
 */
class ConfigComponent extends Component {
	/**
	 * Components
	 * @var array
	 */
	public $components = array('Session' => array('className' => 'MeTools.MeSession'));
	
	/**
	 * Controller
	 * @var Object, controller 
	 */
	protected $controller;
	
	/**
	 * Checks if debugging for localhost should be forced.
	 * @return bool TRUE if debugging should be forced, otherwise FALSE
	 */
	protected function _debugForLocalhost() {
		return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')) && Configure::read('MeCms.main.debug_on_localhost');
	}
	
	/**
	 * Turns a string of words separated by commas (and optional spaces) into an array.
	 * 
	 * For example:
	 * <pre>'alfa, beta, gamma'</pre>
	 * 
	 * Becomes:
	 * <code>array(
	 *	(int) 0 => 'alfa',
	 *	(int) 1 => 'beta',
	 *	(int) 2 => 'gamma'
	 * )
	 * </code>
	 * @param string $string String of words separated by commas (and optional spaces)
	 * @return mixed Array of values
	 */
	protected function _stringAsArray($string) {
		if(!is_string($string))
			return $string;
		
		$array = explode(',', preg_replace('/\s/', NULL, trim($string)));
		
		return empty($array) ? $string : $array;
	}
	
	/**
	 * Turns some values.
	 * @param array $config Configuration
	 * @return array Configuration
	 * @uses _stringAsArray()
	 * @uses MeToolsAppController::isAction()
	 */
	protected function _turnsValues($config) {
		//Turns some values
		$config['users']['activation'] = is_numeric($value = $config['users']['activation']) && $value >= 0 && $value <= 2 ? $value : 1;
		$config['users']['default_group'] = is_numeric($value = $config['users']['default_group']) ? $config['users']['default_group'] : 3;

		//Turns some values as array		
		$config['backend']['topbar'] = $this->_stringAsArray($config['backend']['topbar']);
		$config['frontend']['widgets'] = $this->_stringAsArray($config['frontend']['widgets']);
		$config['frontend']['widgets_homepage'] = $this->_stringAsArray($config['frontend']['widgets_homepage']);
		
		//If the current action is the homepage, the widgets are the homepage widgets
		if($this->controller->isAction(array('home', 'homepage', 'main')) && is_array($config['frontend']['widgets_homepage']))
			$config['frontend']['widgets'] = $config['frontend']['widgets_homepage'];
		
		//Deletes useless values
		unset($config['frontend']['widgets_homepage']);
		
		return $config;
	}


	/**
	 * Loads the configuration file. 
	 * @return array Configuration
	 */
	protected function load() {
		//Loads from plugin (`APP/Plugin/MeCms/Config/mecms.php`)
		Configure::load('MeCms.mecms');

		//Loads from the app, if exists (`APP/Config/mecms.php`).
		//This configuration will overwrite the one obtained by the plugin
		if(is_readable(APP.'Config'.DS.'mecms.php'))
			Configure::load('mecms');
		
		return Configure::read('MeCms');
	}

	/**
	 * Is called after the controller executes the requested action's logic, 
	 * but before the controller's renders views and layout.
	 * @param Controller $controller
	 * @see http://api.cakephp.org/2.6/class-Component.html#_beforeRender CakePHP Api
	 */
	public function beforeRender(Controller $controller) {
		//Sets the configuration for the view
		$controller->set('config', $controller->config);
	}

	/**
     * Called before the controller's beforeFilter method.
	 * @param Controller $controller
     * @see http://api.cakephp.org/2.6/class-Component.html#_initialize CakePHP Api
	 * @uses MeCmsAppController::config
	 * @uses controller
	 * @uses _debugForLocalhost()
	 * @uses _turnsValues()
	 * @uses load()
	 */
	public function initialize(Controller $controller) {
		//Sets the controller
		$this->controller = $controller;
				
		//Loads the configuration values
		$config = $this->load();
		
		//Turns some values
		$config = $this->_turnsValues($config);
		
		//Writes
		Configure::write('MeCms', $config);
		
		//Sets debug
		Configure::write('debug', $config['main']['debug'] || $this->_debugForLocalhost() ? 2 : 0);
		
		//Sets cache
		Configure::write('Cache.disable', !$config['main']['cache']);
		
		//Sets the configuration so that the controller can read it
		$controller->config = $config;
	}
	
	/**
	 * Sets the configuration for KCFinder.
	 * @param array $options Options
	 * @return bool
	 * @uses MeAuthComponent::isAdmin()
	 */
	public function kcfinder($options = array()) {
		$default = array(
			'denyExtensionRename'	=> TRUE,
			'denyUpdateCheck'		=> TRUE,
			'dirnameChangeChars'	=> array(' ' => '_', ':' => '_'),
			'disabled'				=> FALSE,
			'filenameChangeChars'	=> array(' ' => '_', ':' => '_'),
			'jpegQuality'			=> 100,
			'uploadDir'				=> WWW_ROOT.'files',
			'uploadURL'				=> Router::url('/files', TRUE),
			'types'					=> Configure::read('MeCms.kcfinder.types')
		);
		
		//If the user is not and admin
		if(!$this->controller->Auth->isAdmin()) {
			//Only admins can delete or rename directories
			$default['access']['dirs'] = array('create' => TRUE, 'delete' => FALSE, 'rename' => FALSE);
			//Only admins can delete, move or rename files
			$default['access']['files'] = array(
				'upload'	=> TRUE,
				'delete'	=> FALSE,
				'copy'		=> TRUE,
				'move'		=> FALSE,
				'rename'	=> FALSE
			);
		}

		return $this->Session->write('KCFINDER', am($default, $options));
	}
}