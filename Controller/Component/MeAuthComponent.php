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

App::uses('AuthComponent', 'Controller/Component');

/**
 * Authentication control component class
 *
 * Binds access control with user authentication and session management.
 * 
 * Rewrites `AuthComponent` provided by CakePHP.
 */
class MeAuthComponent extends AuthComponent {
	/**
	 * Currant action name
	 * @var string
	 */
	static public $action;
	
    /**
     * Called before the controller's beforeFilter method.
     * @param Controller $controller
     * @see http://api.cakephp.org/2.5/class-Component.html#_initialize CakePHP Api
     */	
	public function initialize(Controller $controller) {
		parent::initialize($controller);
		
		//Sets the current action name
		self::$action = $controller->request->params['action'];
	}

	/**
	 * Checks whether the user has a specific id
	 * @param type $id
	 * @return boolean
	 */
	static public function hasId($id) {
		if(empty(self::user('id')))
			return FALSE;
		
		return (int) self::user('id') === (int) $id;
	}
	
	/**
	 * Checks whether the user has a specific level
	 * @param type $level
	 * @return boolean
	 */
	static public function hasLevel($level) {
		if(empty(self::user('Group.level')))
			return FALSE;
		
		return (int) self::user('Group.level') >= (int) $level;
	}
	
	/**
	 * Checks if an action is the current action.
	 * 
	 * Example:
	 * <code>
	 * $this->Auth->isAction('delete');
	 * </code>
	 * It returns TRUE if the current action is `admin_delete`, otherwise FALSE.
	 * 
	 * Example:
	 * <code>
	 * $this->Auth->isAction('edit', 'delete');
	 * </code>
	 * It returns TRUE if the current action is `admin_edit` or `admin_delete`, otherwise FALSE.
	 * @return type TRUE if the action to check is the current action, otherwise FALSE
	 */
	static public function isAction() {
		$actions = func_get_args();
		
		array_walk($actions, function(&$v) {
			$v = sprintf('admin_%s', $v);
		});
		
		return in_array(self::$action, $actions);
	}
	
	/**
	 * Checks whether the user is an administrator
	 * @return boolean
	 */
	static public function isAdmin() {
		if(empty(self::user('group_id')) && empty(self::user('Group.name')))
			return FALSE;
		
		return self::user('group_id') === 1 || self::user('Group.name') === 'admin';
	}
	
	/**
	 * Checks whether the user is the admin founder
	 * @return boolean
	 */
	static public function isFounder() {
		if(empty(self::user('id')))
			return FALSE;
		
		return self::user('id') === 1;
	}
	
	/**
	 * Checks whether the user is logged
	 * @return type
	 */
	static public function isLogged() {
		return !empty(self::user('id'));
	}
	
	/**
	 * Checks whether the user is a manager (manager or administrator)
	 * @return boolean
	 */
	static public function isManager() {
		if(empty(self::user('group_id')) && empty(self::user('Group.name')))
			return FALSE;
		
		return self::user('group_id') <= 2 || self::user('Group.name') === 'admin' || self::user('Group.name') === 'manager';
	}
}