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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Validation;

use MeCms\Model\Validation\AppValidator;

class BannerValidator extends AppValidator {
	/**
	 * Construct.
	 * 
	 * Adds some validation rules.
	 * @uses MeCms\Model\Validation\AppValidator::__construct()
	 */
    public function __construct() {
        parent::__construct();
		
		//Filename
		$this->add('filename', ['extension' => [
			'message'	=> __d('me_cms', 'Valid extensions: {0}', 'gif, jpg, jpeg, png'),
			'rule'		=> ['extension', ['gif', 'jpg', 'jpeg', 'png']]
		]])->requirePresence('filename', 'create');
		
		//Position
		$this->add('position_id', ['naturalNumber' => [
			'message'	=> __d('me_cms', 'You have to select a valid option'),
			'rule'		=> 'naturalNumber'
		]])->requirePresence('position_id', 'create');
		
		//Target
		$this->add('target', [
			'maxLength' => [
				'message'	=> __d('me_cms', 'Must be at most {0} chars', 255),
				'rule'		=> ['maxLength', 255]
			],
			'url' => [
				'message'	=> __d('me_cms', 'Must be a valid url'),
				'rule'		=> ['url', TRUE]
			]
		])->allowEmpty('target');

        return $this;
	}	
}