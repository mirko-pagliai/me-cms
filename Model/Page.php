<?php
/**
 * Page
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
App::uses('CakeTime', 'Utility');

/**
 * Page Model
 */
class Page extends MeCmsAppModel {
	/**
	 * Display field
	 * @var string
	 */
	public $displayField = 'title';
	
	/**
	 * Order
	 * @var array 
	 */
	public $order = array('title' => 'ASC');
	
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
		'title' => array(
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 3, 100)
			),
			'isUnique' => array(
				'message'	=> 'This value is already used',
				'rule'		=> 'isUnique'
			)
		),
		'subtitle' => array(
			'allowEmpty'	=> TRUE,
			'message'		=> 'Must be at most %d chars',
			'rule'			=> array('maxLength', 150)
		),
		'slug' => array(
			'slug' => array(
				'last'		=> FALSE,
				'message'	=> 'Allowed chars: lowercase letters, numbers, dash',
				'rule'		=> array('custom', '/^[a-z0-9\-]+$/')
			),
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 3, 100)
			),
			'isUnique' => array(
				'message'	=> 'This value is already used',
				'rule'		=> 'isUnique'
			)
		),
		'text' => array(
			'message'	=> 'This field can not be empty',
			'rule'		=> array('notEmpty')
		),
		'priority' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> array('range', 0, 6)
		),
		'active' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> array('boolean')
		),
		'created' => array(
			'allowEmpty'	=> TRUE,
			'message'		=> 'Must be a valid datetime',
			'rule'			=> array('datetime')
		),
		'modified' => array(
			'message'	=> 'Must be a valid datetime',
			'rule'		=> array('datetime')
		)
	);
	
	/**
	 * Called after every deletion operation.
	 */
	public function afterDelete() {
		Cache::clearGroup('pages', 'pages');
	}
	
	/**
	 * Called after each successful save operation.
	 * @param boolean $created TRUE if this save created a new record
	 * @param array $options Options passed from Model::save()
	 */
	public function afterSave($created, $options = array()) {
		Cache::clearGroup('pages', 'pages');
	}
}