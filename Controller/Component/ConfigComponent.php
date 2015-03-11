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
	 * Configuration
	 * @var array
	 */
	protected $config;

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
	 * Loads and sets the configuration
	 * @uses config
	 */
	protected function _loadConfig() {
		//Loads from plugin (`APP/Plugin/MeCms/Config/mecms.php`)
		Configure::load('MeCms.mecms');
		
		//Loads from the app, if exists (`APP/Config/mecms.php`).
		//This configuration will overwrite the one obtained by the plugin
		if(is_readable(APP.'Config'.DS.'mecms.php')) {
			$config = Configure::read('MeCms');
		
			Configure::load('mecms', 'default', false);
			
			foreach($config as $key => $value) {
				if(!empty(Configure::read($key = sprintf('MeCms.%s', $key))))
					$value = am($value, Configure::read($key));
				
				Configure::write($key, $value);
			}
		}
		
		$this->config = Configure::read('MeCms');
	}
	
	/**
	 * Sets widgets
	 * @uses config
	 * @uses _stringAsArray()
	 */
	protected function _setWidgets() {
		//If the current action is the homepage, the widgets are the homepage widgets
		if($this->controller->isAction(array('home', 'homepage', 'main')) && is_array($this->config['frontend']['widgets_homepage']))
			$widgets = $this->config['frontend']['widgets_homepage'];
		else
			$widgets = $this->config['frontend']['widgets'];
		
		//Turns it into array, if it's a string
		$widgets = $this->_stringAsArray($widgets);
		
		//Resets widgets
		$this->config['frontend']['widgets'] = array();
		
		foreach($widgets as $k => $widget) {
			//If the widget is an array, then the key element is the widget name and the value element is the widget options
			if(is_array($widget))
				$this->config['frontend']['widgets'][$k] = array('name' => $k, 'options' => $widget);
			else
				$this->config['frontend']['widgets'][$widget] = array('name' => $widget);
		}
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
	 * @uses config
	 * @uses _stringAsArray()
	 * @uses MeToolsAppController::isAction()
	 */
	protected function _turnsValues() {
		//Turns some values
		$this->config['users']['activation'] = is_numeric($value = $this->config['users']['activation']) && $value >= 0 && $value <= 2 ? $value : 1;
		$this->config['users']['default_group'] = is_numeric($value = $this->config['users']['default_group']) ? $this->config['users']['default_group'] : 3;

		//Turns some values as array		
		$this->config['backend']['topbar'] = $this->_stringAsArray($this->config['backend']['topbar']);
		
		//Deletes useless values
		unset($this->config['frontend']['widgets_homepage']);
	}

	/**
	 * Is called after the controller executes the requested action's logic, 
	 * but before the controller's renders views and layout.
	 * @param Controller $controller
	 * @see http://api.cakephp.org/2.6/class-Component.html#_beforeRender CakePHP Api
	 * @uses config
	 */
	public function beforeRender(Controller $controller) {
		//Sets the configuration for the view
		$controller->set('config', $this->config);
	}

	/**
     * Called before the controller's beforeFilter method.
	 * @param Controller $controller
     * @see http://api.cakephp.org/2.6/class-Component.html#_initialize CakePHP Api
	 * @uses config
	 * @uses controller
	 * @uses MeCmsAppController::config
	 * @uses _debugForLocalhost()
	 * @uses _setWidgets()
	 * @uses _loadConfig()
	 * @uses _turnsValues()
	 */
	public function initialize(Controller $controller) {
		//Sets the controller
		$this->controller = $controller;
				
		//Loads and sets the configuration
		$this->_loadConfig();
		
		//Sets widgets
		$this->_setWidgets();
		
		//Turns some values
		$this->_turnsValues();
		
		//Writes
		Configure::write('MeCms', $this->config);
		
		//Sets debug
		Configure::write('debug', $this->config['main']['debug'] || $this->_debugForLocalhost() ? 2 : 0);
		
		//Sets cache
		Configure::write('Cache.disable', !$this->config['main']['cache']);
		
		//Sets the configuration so that the controller can read it
		$controller->config = $this->config;
	}
	
	/**
	 * Sets the configuration for KCFinder.
	 * @param array $options Options
	 * @return bool
	 * @uses MeAuthComponent::isAdmin()
	 */
	public function kcfinder($options = array()) {
		if($this->Session->check('KCFINDER'))
			return TRUE;
		
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