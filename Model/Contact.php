<?php
/**
 * Contact
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
 * @package		MeCms\Model
 */

App::uses('MeCmsAppModel', 'MeCms.Model');

/**
 * Contact Model.
 * 
 * This is not a real model, it's just to have some validation rules for contact forms.
 */
class Contact extends MeCmsAppModel {
	/**
	 * This model does not use a database table
	 * @var bool 
	 */
	public $useTable = FALSE;
	
	/**
	 * Validation rules
	 * @var array
	 */
	public $validate = array(
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
			)
		),
		'first_name' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 2, 40)
			),
			'first_name' => array(
				'message'	=> 'Allowed chars: letters, apostrophe, space',
				'rule'		=> array('custom', '/^[A-z\'\ ]+$/')
			)
		),
		'last_name' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 2, 40)
			),
			'last_name' => array(
				'message'	=> 'Allowed chars: letters, apostrophe, space',
				'rule'		=> array('custom', '/^[A-z\'\ ]+$/')
			)
		),
		'message' => array(
			'message'	=> 'Must be between %d and %d chars',
			'rule'		=> array('between', 10, 1000)
		)
	);
}