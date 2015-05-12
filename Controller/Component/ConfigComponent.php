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
App::uses('Plugin', 'MeTools.Utility');

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
	 * Loads and sets the configuration
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
				if(Configure::check($key = sprintf('MeCms.%s', $key)))
					$value = am($value, Configure::read($key));
				
				Configure::write($key, $value);
			}
		}
		
		//Turns value
		if(!is_numeric(Configure::read($key = 'MeCms.users.activation')) || Configure::read($key) > 2)
			Configure::write($key, 1);
		
		//Turns value
		if(!is_numeric(Configure::read($key = 'MeCms.users.default_group')))
			Configure::write($key, 3);
			
		//Turns value as array		
		Configure::write($key = 'MeCms.backend.topbar', $this->_stringAsArray(Configure::read($key)));
	}
	
	/**
	 * Loads and sets the widget map configuration
	 * @uses Plugin::getAll()
	 * @uses Plugin::getPath()
	 */
	protected function _loadWidgetsMap() {
		//Loads from all plugins
		foreach(Plugin::getAll() as $plugin)
			if(is_readable(Plugin::getPath($plugin).'Config'.DS.'widgets_map.php'))
				Configure::load(sprintf('%s.widgets_map', $plugin));
		
		foreach($map = Configure::read('WidgetsMap') as $name => $method) {
			list($class, $method) = explode('::', $method);

			$component = array_values(pluginSplit($class))[1];

			$map[$name] = compact('class', 'component', 'method');
		}
		
		Configure::write('WidgetsMap', $map);
	}
	
	/**
	 * Sets widgets
	 * @uses controller
	 * @uses _stringAsArray()
	 */
	protected function _setWidgets() {
		//If the current action is the homepage, the widgets are the homepage widgets
		if($this->controller->isAction(array('home', 'homepage', 'main')) && is_array(Configure::read('MeCms.frontend.widgets_homepage')))
			$widgets = Configure::read('MeCms.frontend.widgets_homepage');
		else
			$widgets = Configure::read('MeCms.frontend.widgets');
		
		//Deletes useless values
		Configure::delete('MeCms.frontend.widgets_homepage');
		
		//Turns it into array, if it's a string
		$widgets = $this->_stringAsArray($widgets);
		
		$widgetsTmp = array();

		foreach($widgets as $k => $widget) {
			//If the widget is an array, then the key element is the widget name and the value element is the widget options
			if(is_array($widget))
				$widgetsTmp[$k] = array('name' => $k, 'options' => $widget);
			else
				$widgetsTmp[$widget] = array('name' => $widget);
		}
		
		Configure::write('MeCms.frontend.widgets', $widgetsTmp);
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
	 * Is called after the controller executes the requested action's logic, 
	 * but before the controller's renders views and layout.
	 * @param Controller $controller
	 * @see http://api.cakephp.org/2.6/class-Component.html#_beforeRender CakePHP Api
	 */
	public function beforeRender(Controller $controller) {
		//Sets the configuration for the view
		$controller->set('config', Configure::read('MeCms'));
	}

	/**
     * Called before the controller's beforeFilter method.
	 * @param Controller $controller
     * @see http://api.cakephp.org/2.6/class-Component.html#_initialize CakePHP Api
	 * @uses controller
	 * @uses MeCmsAppController::config
	 * @uses _debugForLocalhost()
	 * @uses _loadConfig()
	 * @uses _loadWidgetsMap()
	 * @uses _setWidgets()
	 * @uses _turnsValues()
	 */
	public function initialize(Controller $controller) {
		//Sets the controller instance
		$this->controller = $controller;
				
		//Loads and sets the configuration
		$this->_loadConfig();
		
		//Sets widgets
		$this->_setWidgets();
		
		//Loads and sets the widgets map
		$this->_loadWidgetsMap();
		
		//Sets debug		
		Configure::write('debug', Configure::read('MeCms.main.debug') || $this->_debugForLocalhost() ? 2 : 0);
		
		//Sets cache
		Configure::write('Cache.disable', !Configure::read('MeCms.main.cache'));
				
		//Sets the configuration so that the controller can read it
		$controller->config = Configure::read('MeCms');
		$controller->widgetsMap = Configure::read('WidgetsMap');
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