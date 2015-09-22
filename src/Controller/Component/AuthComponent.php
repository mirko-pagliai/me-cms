<?php
/**
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
 * @see			http://api.cakephp.org/3.0/class-Cake.Controller.Component.AuthComponent.html
 */
namespace MeCms\Controller\Component;

use Cake\Controller\Component\AuthComponent as CakeAuthComponent;
use Cake\Controller\ComponentRegistry;

/**
 * Authentication control component class.
 *
 * Binds access control with user authentication and session management.
 * 
 * Rewrites {@link http://api.cakephp.org/3.0/class-Cake.Controller.Component.AuthComponent.html AuthComponent}.
 */
class AuthComponent extends CakeAuthComponent {	
	/**
	 * User data
	 * @var array 
	 */
	static protected $user = FALSE;
	
	/**
	 * Constructor
	 * @param ComponentRegistry $registry A ComponentRegistry this component can use to lazy load its components
	 * @param array $config Array of configuration settings
	 */
	public function __construct(ComponentRegistry $registry, array $config = []) {
		//Sets config
		$config = am([
			'authenticate'			=> ['Form' => ['contain' => 'Groups', 'userModel' => 'MeCms.Users']],
			'authError'				=> __d('me_cms', 'You are not authorized for this action'),
			'authorize'				=> 'Controller',
			'flash'					=> ['element' => 'MeTools.error'],
			'loginAction'			=> ['_name' => 'login'],
			'loginRedirect'			=> ['_name' => 'dashboard'],
			'logoutRedirect'		=> ['_name' => 'homepage'],
			'unauthorizedRedirect'	=> ['_name' => 'dashboard']
		], $config);
		
		parent::__construct($registry, $config);
	}
	
	/**
	 * Method that is called automatically when the method doesn't exist.
	 * 
	 * This method provides aliases for the `isGroup()` method. For example:
	 * <code>
	 * $this->Auth->isAdmin()
	 * </code>
	 * will call:
	 * <code>
	 * $this->Auth->isGroup('admin');
	 * </code>
	 * @param string $method Method to invoke
	 * @param array $params Array of params for the method
	 * @uses isGroup()
	 */
	public function __call($method, $params) {
		preg_match('/^is([A-Z][a-z]+)$/', $method, $matches);
		
		if(!empty($matches[1]))
			return self::isGroup(strtolower($matches[1]));
	}
	
	/**
	 * Method that is called automatically when the method doesn't exist.
	 * 
	 * See the `__call()` method for examples.
	 * @param string $method Method to invoke
	 * @param array $params Array of params for the method
	 * @see __call()
	 * @uses isGroup()
	 */
	public static function __callStatic($method, $params) {
		preg_match('/^is([A-Z][a-z]+)$/', $method, $matches);
		
		if(!empty($matches[1]))
			return self::isGroup(strtolower($matches[1]));
	}
	
	/**
	 * Constructor hook method
	 * @param array $config The configuration settings provided to this component
	 * @see http://api.cakephp.org/3.0/class-Cake.Controller.Component.html#_initialize
	 * @uses Cake\Controller\Component\AuthComponent::user()
	 * @uses $user
	 */
	public function initialize(array $config) {
		parent::initialize($config);
		
		//The authorization error is shown only if the user is already logged in and he is trying to do something not allowed
		if(!self::user())
			$this->config('authError', FALSE);
		
		//Gets the user data
		self::$user = self::user();
	}

	/**
	 * Checks whether the user has a specific ID.
	 * 
	 * You can pass the ID as a string or as an array of IDs.
	 * In this case, will be sufficient that the user has one of the IDs.
	 * @param string|array $id User ID as string or array
	 * @return boolean
	 * @uses $user
	 */
	static public function hasId($id) {
		if(empty(self::$user['id']))
			return FALSE;
		
		return in_array(self::$user['id'], is_array($id) ? $id : [$id]);
	}
	
	/**
	 * Checks whether the user is the admin founder
	 * @return boolean
	 * @uses $user
	 */
	static public function isFounder() {
		if(empty(self::$user['id']))
			return FALSE;
		
		return (int) self::$user['id'] === 1;
	}
	
	/**
	 * Checks whether the user is logged
	 * @return boolean
	 * @uses $user
	 */
	static public function isLogged() {
		return !empty(self::$user['id']);
	}
	
	/**
	 * Checks whether the user belongs to a group.
	 * 
	 * You can pass the group as a string or as an array of groups.
	 * In this case, will be sufficient that the user belongs to one of the groups.
	 * @param string|array $group User group as string or array
	 * @return boolean
	 * @uses $user
	 */
	static public function isGroup($group) {
		if(empty(self::$user['group']['name']))
			return FALSE;
		
		return in_array(self::$user['group']['name'], is_array($group) ? $group : [$group]);
	}
}