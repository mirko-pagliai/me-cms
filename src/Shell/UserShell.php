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
 */
namespace MeCms\Shell;

use MeTools\Console\Shell;

/**
 * Allows the user management 
 */
class UserShell extends Shell {
	/**
	 * Adds an user
	 */
	public function add() {
		$this->loadModel('MeCms.Users');
		
		//Gets user groups
		$groups = $this->Users->Groups->find('list')->toArray();
		
		//Checks for user groups
		if(empty($groups))
			$this->error(__d('me_cms', 'Before you can manage users, you have to create at least a user group'));
				
		while(1) {
			$user = [];
			
			//Asks for some fields
			$user['username'] = $this->in(__d('me_cms', 'Username'));
			$user['password'] = $this->in(__d('me_cms', 'Password'));
			$user['password_repeat'] = $this->in(__d('me_cms', 'Repeat password'));
			$user['email'] = $this->in(__d('me_cms', 'Email'));
			$user['first_name'] = $this->in(__d('me_cms', 'First name'));
			$user['last_name'] = $this->in(__d('me_cms', 'Last name'));

			//Asks for group, if not passed as option
			if(empty($this->params['group'])) {
				//Formats groups
				$groups = array_map(function($group, $id) {
					return [$id, $group];
				}, $groups, array_keys($groups));
				
				//Sets header
				$header = ['ID', 'Name'];
				
				//Prints as table
				//See @http://book.cakephp.org/3.0/en/console-and-shells/helpers.html#table-helper
				$this->helper('table')->output(am([$header], $groups));

				$user['group_id'] = $this->in(__d('me_cms', 'Group ID'));
			}
			else
				$user['group_id'] = $this->params['group'];

			//Checks fields
			foreach($user as $value)
				if(empty($value))
					$this->error(__d('me_cms', 'Some fields are missing. Try again'));

			//Checks the group IDS
			if(!array_key_exists($user['group_id'], $groups))
				$this->error(__d('me_cms', 'Invalid group ID'));
				
			//Saves the user
			if($this->Users->save($this->Users->newEntity($user))) {
				$this->success(__d('me_cms', 'The user has been saved'));
				break;
			}
			else {
				$this->err(__d('me_cms', 'An error occurred, try again'));
				$this->err(__d('me_cms', 'The user could not be saved'));
			}
		}
	}
	
	/**
	 * Lists users
	 */
	public function index() {
		$this->loadModel('MeCms.Users');
		
		//Gets users
		$users = $this->Users->find()
			->contain(['Groups' => ['fields' => ['label']]])
			->select(['id', 'username', 'email', 'first_name', 'last_name', 'active', 'banned', 'post_count', 'created'])
			->toArray();
		
		//Checks for users
		if(empty($users))
			$this->error(__d('me_cms', 'There are no users'));
		
		//Sets header
		$header = [
			__d('me_cms', 'ID'),
			__d('me_cms', 'Username'),
			__d('me_cms', 'Name'),
			__d('me_cms', 'Email'),
			__d('me_cms', 'Posts'),
			__d('me_cms', 'Status'),
			__d('me_cms', 'Date')
		];
		
		//Formats users
		$users = array_map(function($user) {
			//Sets the user status
			if($user['banned'])
				$user['status'] = __d('me_cms', 'Banned');
			elseif(!$user['active'])
				$user['status'] = __d('me_cms', 'Pending');
			else
				$user['status'] = __d('me_cms', 'Active');
			
			return [
				$user['id'],
				$user['username'],
				$user['full_name'],
				$user['email'],
				$user['post_count'],
				$user['status'],
				$user['created']
			];
		}, $users);
		
		//Prints as table
		//See @http://book.cakephp.org/3.0/en/console-and-shells/helpers.html#table-helper
		$this->helper('table')->output(am([$header], $users));
	}
	
	/**
	 * Gets the option parser instance and configures it.
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		
		return $parser->addSubcommands([
			'add' => [
				'help' => __d('me_cms', 'Adds an user'),
				'parser' => [ 'options' => [
					'group' => [
						'short' => 'g',
						'help' => __d('me_cms', 'Group ID'),
					]
				]]
			],
			'index' => ['help' => __d('me_cms', 'Lists users')]
		]);
	}
}