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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
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
	 * Checks if debugging for localhost should be forced
	 * @param Controller $controller
	 * @return bool TRUE if debugging should be forced, otherwise FALSE
	 */
	protected function _debugForLocalhost(Controller $controller) {
		return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')) && Configure::read('MeCms.debug_on_localhost');
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
	protected function _turnsAsArray($string) {		
		if(empty(trim($string)))
			return $string;
		
		return explode(',', preg_replace('/\s/', NULL, trim($string)));
	}

	/**
	 * Loads and writes the configuration.
	 * @uses MeCmsAppController::isAdminRequest()
	 * @uses _turnsAsArray()
	 */
	protected function _writeConfig(Controller $controller) {
		//Loads the configuration from the plugin (`APP/Plugin/MeCms/Config/mecms.php`)
		Configure::load('MeCms.mecms');

		//Loads the configuration from the app, if exists (`APP/Config/mecms.php`).
		//This configuration will overwrite the one obtained by the plugin
		if(is_readable(APP.'Config'.DS.'mecms.php'))
			Configure::load('mecms');

		//Turns some values as array
		Configure::write($key = 'MeCms.backend.topbar', $this->_turnsAsArray(Configure::read($key)));
		Configure::write($key = 'MeCms.frontend.widgets', $this->_turnsAsArray(Configure::read($key)));

		//If the current action is the homepage and the homepage widgets have been set, it overrides the widgets with the homepage widgets
		if(in_array($controller->request->params['action'], array('home', 'homepage', 'main')) && Configure::read('MeCms.frontend.widgets_homepage'))
			Configure::write('MeCms.frontend.widgets', $this->_turnsAsArray(Configure::read('MeCms.frontend.widgets_homepage')));

		//Deletes the homepage widgets key
		Configure::delete('MeCms.frontend.widgets_homepage');
		
		//If it's an admin request
		if($controller->isAdminRequest())
			Configure::write('MeCms', am(Configure::read('MeCms.backend'), Configure::read('MeCms.general')));
		//Else, if it is not ad admin request
		else
			Configure::write('MeCms', am(Configure::read('MeCms.frontend'), Configure::read('MeCms.general')));
	}

	/**
	 * Is called after the controller executes the requested action's logic, 
	 * but before the controller's renders views and layout.
	 * @param Controller $controller
	 */
	public function beforeRender(Controller $controller) {
		//Sets the configuration for the view
		$controller->set('config', Configure::read('MeCms'));
	}
	
    /**
     * Called before the controller's beforeFilter method.
     * @param Controller $controller
     * @see http://api.cakephp.org/2.5/class-Component.html#_initialize CakePHP Api
	 * @uses _debugForLocalhost()
	 * @uses _writeConfig()
	 * @uses MeCmsAppController::config
     */	
	public function initialize(Controller $controller) {
		//Writes the configuration
		$this->_writeConfig($controller);
		
		//Sets debug
		if(Configure::read('MeCms.debug') || $this->_debugForLocalhost($controller))
			Configure::write('debug', 2);
		else
			Configure::write('debug', 0);
			
		//Sets cache
		Configure::write('Cache.disable', !Configure::read('MeCms.cache'));
		//Sets the session timeout
		Configure::write('Session.timeout', Configure::read('MeCms.timeout'));
		
		//Sets the configuration so that the controller can read it
		$controller->config = Configure::read('MeCms');
	}
}