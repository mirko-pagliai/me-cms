<?php
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
App::uses('MeCmsBackendAppModel', 'MeCmsBackend.Model');

/**
 * User
 *
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Model
 */

/**
 * User Model
 */
class User extends MeCmsBackendAppModel {
	/**
	 * Display field
	 * @var string
	 */
	public $displayField = 'username';
	
	/**
	 * Order
	 * @var array 
	 */
	public $order = array('username' => 'ASC');
	
	/**
	 * Virtual fields
	 * @var array
	 */
	public $virtualFields = array('full_name' => 'CONCAT(User.first_name, " ", User.last_name)');

	/**
	 * Validation rules
	 * @var array
	 */
	public $validate = array(
		'id' => array(
			//Blank on create
			'on'	=> 'create',
			'rule'	=> 'blank'
		),
		'group_id' => array(
			'message'		=> 'You have to select a valid option',
			'rule'			=> 'naturalnumber'
		),
		'username' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 3, 40)
			),
			'isUnique' => array(
				'last'		=> FALSE,
				'message'	=> 'This value is already used',
				'rule'		=> 'isUnique'
			),
			'username' => array(
				'message'	=> 'Allowed chars: lowercase letters, numbers, dash',
				'rule'		=> array('custom', '/^[a-z0-9\-]+$/')
			),
		),
		'email' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 3, 100)
			),
			'email' => array(
				'last'		=> FALSE,
				'rule'		=> 'email',
				'message'	=> 'You have to enter a valid value',
			),
			'isUnique' => array(
				'message'	=> 'This value is already used',
				'rule'		=> 'isUnique'
			)
		),
		'password' => array(
			//On "create", the field must be filled
			'minLengthOnCreate' => array(
				'allowEmpty'	=> FALSE,
				'message'		=> 'Must be at least %d characters',
				'on'			=> 'create',
				'rule'			=> array('minLength', 8)
			),
			//On "update", the field can be left blank
			'minLengthOnUpdate' => array(
				'allowEmpty'	=> TRUE,
				'message'		=> 'Must be at least %d characters',
				'on'			=> 'update',
				'rule'			=> array('minLength', 8)
			)
		),
		//This is used to check that the password has been correctly inserted
		'password_repeat' => array(
			//On "create", the field must be filled
			'passwordsMatchOnCreate' => array(
				'allowEmpty'	=> FALSE,
				'message'		=> 'Passwords don\'t match',
				'on'			=> 'create',
				'rule'			=> 'passwordsMatch'
			),
			//On "update", the field can be left blank.
			//If the "password" field is not blank, then this field must also be filled out.
			//This is set by the "beforeValidate()" callback method
			'passwordsMatchOnUpdate' => array(
				'allowEmpty'	=> TRUE,
				'message'		=> 'Passwords don\'t match',
				'on'			=> 'update',
				'rule'			=> 'passwordsMatch'
			)
		),
		//This is only used when a user changes his password
		'old_password' => array(
			'message'	=> 'The old password is wrong',
			'rule'		=> 'oldPasswordIsRight'
		),
		'first_name' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 2, 40)
			),
			'first_name' => array(
				'message'	=> 'Allowed chars: letters, comma, apostrophe',
				'rule'		=> array('custom', '/^[A-z\',\ ]+$/')
			)
		),
		'last_name' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 2, 40)
			),
			'last_name' => array(
				'message'	=> 'Allowed chars: letters, comma, apostrophe',
				'rule'		=> array('custom', '/^[A-z\',\ ]+$/')
			)
		),
		'active' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> array('boolean')
		),
		'banned' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> array('boolean')
		),
		'created' => array(
			'message'	=> 'Must be a valid date',
			'rule'		=> array('datetime')
		),
		'modified' => array(
			'message'	=> 'Must be a valid date',
			'rule'		=> array('datetime')
		)
	);

	/**
	 * belongsTo associations
	 * @var array
	 */
	public $belongsTo = array(
		'Group' => array(
			'className' => 'MeCmsBackend.UsersGroup',
			'foreignKey' => 'group_id',
			'counterCache' => TRUE
		)
	);

	/**
	 * hasMany associations
	 * @var array
	 */
	public $hasMany = array(
		'Post' => array(
			'className' => 'MeCmsBackend.Post',
			'foreignKey' => 'user_id',
			'dependent' => FALSE
		)
	);

	/**
	 * Called before each save operation, after validation. Return a non-true result to halt the save.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 */
	public function beforeSave($options = array()) {
		//Password hash
		if(!empty($this->data[$this->alias]['password'])) {
			$passwordHasher = new BlowfishPasswordHasher();
			$this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
		}
		
		return TRUE;
	}
	
	/**
	 * Called during validation operations, before validation.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if validate operation should continue, FALSE to abort
	 */
	public function beforeValidate($options = array()) {
		//If the "password" field is not blank, then the "password_field" field must also be filled out.
		if(!empty($this->data[$this->alias]['password']) && isset($this->data[$this->alias]['password_repeat']))		
			$this->validator()->getField('password_repeat')->getRule('passwordsMatchOnUpdate')->allowEmpty = FALSE;
		
		return TRUE;
	}
	
	/**
	 * Checks if the old password is right.
	 * This is only used when a user changes his password
	 * @param mixed $check Field to be checked
	 * @return boolean TRUE if the old password is right
	 * @see http://stackoverflow.com/a/17252517/1480263
	 */
	function oldPasswordIsRight($check) {		
		//Gets the hash of the old password from the database
		$old_password = $this->field('password', array('User.id' => AuthComponent::user('id')));
		
		//Gets the hash of the old password entered by the user
		$newHash = Security::hash($check['old_password'], 'blowfish', $old_password);
		
		return strcmp($old_password, $newHash) == 0;
	}
	
	/**
	 * Checks if the password has been correctly inserted
	 * @return bool TRUE if they match
	 */
	function passwordsMatch() {
		if(empty($this->data[$this->alias]['password']))
			return FALSE;
		
		return strcmp($this->data[$this->alias]['password'], $this->data[$this->alias]['password_repeat']) == 0;
	}
}