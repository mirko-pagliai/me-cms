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

use Cake\Validation\Validator;

/**
 * Application validator class.
 * Used for validation of model data, it adds some default validation rules.
 * 
 * Example:
 * <code>
 * public function validationDefault(Validator $validator) {
 *		$validator = new \MeCms\Model\Validation\AppValidator;
 * 
 *		return $validator;
 * }
 * </code>
 */
class AppValidator extends Validator {
	/**
	 * Construct.
	 * 
	 * Adds some default validation rules.
	 * @uses Cake\Validation\Validator::__construct()
	 */
    public function __construct() {
        parent::__construct();
		
		//ID
		$this->add('id', 'valid', ['rule' => 'naturalNumber'])
			->allowEmpty('id', 'create');
		
		//User (author)
		$this->add('user_id', ['naturalNumber' => [
			'message'	=> __d('me_cms', 'You have to select a valid option'),
			'rule'		=> 'naturalNumber'
		]]);
		
		//Email
		$this->add('email', [
			'email' => [
				'message'	=> __d('me_cms', 'You have to enter a valid value'),
				'rule'		=> 'email'
			],
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 3, 100),
				'rule'		=> ['lengthBetween', 3, 100]
			]
		]);
		
		//First name
		$this->add('first_name', [
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 3, 40),
				'rule'		=> ['lengthBetween', 3, 40]
			],
			'personName' => [
				'message'	=> sprintf('%s: %s. %s', __d('me_cms', 'Allowed chars'), __d('me_cms', 'letters, apostrophe, space'), __d('me_cms', 'Has to begin with a capital letter')),
				'rule'		=> [$this, 'personName']
			]
		]);
		
		//Last name
		$this->add('last_name', [
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 3, 40),
				'rule'		=> ['lengthBetween', 3, 40]
			],
			'personName' => [
				'message'	=> sprintf('%s: %s. %s', __d('me_cms', 'Allowed chars'), __d('me_cms', 'letters, apostrophe, space'), __d('me_cms', 'Has to begin with a capital letter')),
				'rule'		=> [$this, 'personName']
			]
		]);	
		
		//Title
		$this->add('title', [
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 6, 100),
				'rule'		=> ['lengthBetween', 6, 100]
			],
			'validateUnique' => [
				'message'	=> __d('me_cms', 'This value is already used'),
				'provider'	=> 'table',
				'rule'		=> 'validateUnique'
			]
		]);
		
		//Filename
		$this->add('filename', [
			'blank' => [
				'message'	=> __d('me_cms', 'Can not be changed'),
				'on'		=> 'update',
				'rule'		=> 'blank',
			],
			'maxLength' => [
				'message'	=> __d('me_cms', 'Must be at most {0} chars', 255),
				'rule'		=> ['maxLength', 255]
			],
			'validateUnique' => [
				'message'	=> __d('me_cms', 'This value is already used'),
				'provider'	=> 'table',
				'rule'		=> 'validateUnique'
			]
		]);
		
		//Subtitle
        $this->add('subtitle', ['lengthBetween' => [
			'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 6, 150),
			'rule'		=> ['lengthBetween', 6, 150]
		]])->allowEmpty('subtitle');
		
		//Slug
        $this->add('slug', [
			'lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 6, 100),
				'rule'		=> ['lengthBetween', 6, 100]
			],
			'slug' => [
				'message'	=> sprintf('%s: %s', __d('me_cms', 'Allowed chars'), __d('me_cms', 'lowercase letters, numbers, dash')),
				'rule'		=> [$this, 'slug']
			],
			'validateUnique' => [
				'message'	=> __d('me_cms', 'This value is already used'),
				'provider'	=> 'table',
				'rule'		=> 'validateUnique'
			]
		]);
		
		//Text
        $this->notEmpty('text', __d('me_cms', 'This field can not be empty'));
		
		//Priority
        $this->add('priority', ['range' => [
			'message'	=> __d('me_cms', 'You have to select a valid option'),
			'rule'		=> ['range', 1 ,5]
		]]);
		
		//Description
		$this->add('description', ['maxLength' => [
			'message'	=> __d('me_cms', 'Must be at most {0} chars', 255),
			'rule'		=> ['maxLength', 255]
		]])->allowEmpty('description');
		
		//Active
        $this->add('active', ['boolean' => [
			'message'	=> __d('me_cms', 'You have to select a valid option'),
			'rule'		=> 'boolean'
		]]);
		
		//Created
        $this->add('created', ['datetime' => [
			'message'	=> __d('me_cms', 'You have to enter a valid value'),
			'rule'		=> 'datetime'
		]])->allowEmpty('created');
    }
	
	/**
	 * Lowercase letters validation method.
	 * Checks if a field contains only lowercase letters.
	 * @param string $value Field value
	 * @param array $context Field context
	 * @return bool TRUE if is valid, otherwise FALSE
	 */
	public function lowercaseLetters($value, $context) {
		return (bool) preg_match('/^[a-z]+$', $value);
	}
	
	/**
	 * Person name validation method.
	 * Checks if the name is a valid person name, so contains letters, apostrophe and/or space.
	 * @param string $value Field value
	 * @param array $context Field context
	 * @return bool TRUE if is valid, otherwise FALSE
	 */
	public function personName($value, $context) {
		return (bool) preg_match('/^[A-Z][A-z\'\ ]+$/', $value);
	}
	
	/**
	 * Slug validation method.
	 * Checks if the slug is a valid slug.
	 * @param string $value Field value
	 * @param array $context Field context
	 * @return bool TRUE if is valid, otherwise FALSE
	 */
	public function slug($value, $context) {
		//Lowercase letters, numbers, dash.
		//It must contain at least one letter and must begin and end with a letter or a number.
		return (bool) preg_match('/[a-z]/', $value) && (bool) preg_match('/^[a-z0-9][a-z0-9\-]+[a-z0-9]$/', $value);
	}
}