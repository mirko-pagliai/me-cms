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
	 * Checks whether the user has a specific id
	 * @param type $id
	 * @return boolean
	 */
	public function hasId($id) {
		if(empty($this->user('id')))
			return FALSE;
		
		return (int) $this->user('id') === (int) $id;
	}
	
	/**
	 * Checks whether the user has a specific level
	 * @param type $level
	 * @return boolean
	 */
	public function hasLevel($level) {
		if(empty($this->user('Group.level')))
			return FALSE;
		
		return (int) $this->user('Group.level') >= (int) $level;
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
	public function isAction() {
		$actions = func_get_args();
		
		array_walk($actions, function(&$v) {
			$v = sprintf('admin_%s', $v);
		});
			
		return in_array($this->request->params['action'], $actions);
	}
	
	/**
	 * Checks whether the user is an administrator
	 * @return boolean
	 */
	public function isAdmin() {
		if(empty($this->user('group_id')) && empty($this->user('Group.name')))
			return FALSE;
		
		return $this->user('group_id') === 1 || $this->user('Group.name') === 'admin';
	}
	
	/**
	 * Checks whether the user is the admin founder
	 * @return boolean
	 */
	public function isFounder() {
		if(empty($this->user('id')))
			return FALSE;
		
		return $this->user('id') === 1;
	}
	
	/**
	 * Checks whether the user is logged
	 * @return type
	 */
	public function isLogged() {
		return !empty($this->user('id'));
	}
	
	/**
	 * Checks whether the user is a manager (manager or administrator)
	 * @return boolean
	 */
	public function isManager() {
		debug($this->user('group_id'));
		if(empty($this->user('group_id')) && empty($this->user('Group.name')))
			return FALSE;
		
		return $this->user('group_id') <= 2 || $this->user('Group.name') === 'admin' || $this->user('Group.name') === 'manager';
	}
}