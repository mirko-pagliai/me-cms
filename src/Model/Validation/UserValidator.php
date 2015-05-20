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
namespace MeCms\Model\Validation;

use Cake\ORM\TableRegistry;
use MeCms\Model\Validation\AppValidator;

class UserValidator extends AppValidator {
	/**
	 * Construct.
	 * 
	 * Adds some validation rules.
	 * @uses Cake\Auth\DefaultPasswordHasher::check()
	 * @uses Cake\ORM\TableRegistry::get()
	 * @uses MeCms\Model\Validation\AppValidator::__construct()
	 */
    public function __construct() {
        parent::__construct();
		
		//Users group
        $this->add('group_id', ['naturalNumber' => [
			'message'	=> __d('me_cms', 'You have to select a valid option'),
			'rule'		=> 'naturalNumber'
		]])->requirePresence('group_id', 'create');
		
		//Username
		$this->add('username', [
			'blank' => [
				'message'	=> __d('me_cms', 'Can not be changed'),
				'on'		=> 'update',
				'rule'		=> 'blank',
			],
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 4, 40),
				'rule'		=> ['lengthBetween', 4, 40]
			],
			'slug' => [
				'message'	=> sprintf('%s: %s', __d('me_cms', 'Allowed chars'), __d('me_cms', 'lowercase letters, numbers, dash')),
				'rule'		=> [$this, 'slug']
			],
			'unique' => [
				'message'	=> __d('me_cms', 'This value is already used'),
				'provider'	=> 'table',
				'rule'		=> 'validateUnique'
			],
			'usernameNotReserved' => [
				'message'	=> __d('me_cms', 'This username is reserved'),
				'rule' => function($value, $context) {
					return (bool) !preg_match('/(admin|manager|root|supervisor|moderator)/i', $value);
				}
			]
		])->requirePresence('username', 'create');
		
		//Email
		$this->add('email', [
			'unique' => [
				'message'	=> __d('me_cms', 'This value is already used'),
				'provider'	=> 'table',
				'rule'		=> 'validateUnique'
			]
		])->requirePresence('email', 'create');
		
		//Email repeat
		$this->add('email_repeat', [
			'compareWith' => [
				'message'	=> __d('me_cms', 'Email addresses don\'t match'),
				'rule'		=> ['compareWith', 'email']
			]
		]);
		
		//Password
		$this->add('password', [
			'minLength' => [
				'message'	=> __d('me_cms', 'Must be at least {0} chars', 8),
				'rule'		=> ['minLength', 8]
			],
			'passwordIsStrong' => [
				'message'	=> __d('me_cms', 'The password should contain letters, numbers and symbols'),
				'rule'		=> function($value, $context) {
					return (bool) (preg_match('/[a-z]+/i', $value) && preg_match('/[0-9]+/', $value) && preg_match('/[^a-z0-9]+/i', $value));
				}
			]])->requirePresence('password', 'create');
		
		//Password repeat
		$this->add('password_repeat', [
			'compareWith' => [
				'message'	=> __d('me_cms', 'Passwords don\'t match'),
				'rule'		=> ['compareWith', 'password']
			]])->requirePresence('password_repeat', 'create');
		
		//Old password
		$this->add('password_old', [
			'oldPasswordIsRight' => [
				'message'	=> __d('me_cms', 'The old password is wrong'),
				'rule'		=> function($value, $context) {
					//Gets the old password
					$password = TableRegistry::get('Users')->find()->select(['password'])->where(['id' => $context['data']['id']])->first()->toArray()['password'];
					
					if(empty($password))
						return FALSE;
					
					//Checks if the password matches
					return (new \Cake\Auth\DefaultPasswordHasher)->check($value, $password);
				}
			]
		]);
		
		//First name
		$this->requirePresence('first_name', 'create');
		
		//Last name
		$this->requirePresence('last_name', 'create');
		
		//Banned
        $this->add('banned', ['boolean' => [
			'message'	=> __d('me_cms', 'You have to select a valid option'),
			'rule'		=> 'boolean'
		]])->allowEmpty('banned');

        return $this;
	}
}