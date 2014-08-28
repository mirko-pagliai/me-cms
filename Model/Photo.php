<?php
/**
 * Photo
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

App::uses('MeCmsBackendAppModel', 'MeCmsBackend.Model');
App::uses('Album', 'MeCmsBackend.Utility');

/**
 * Photo Model
 */
class Photo extends MeCmsBackendAppModel {
	/**
	 * Display field
	 * @var string
	 */
	public $displayField = 'filename';

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
		'album_id' => array(
			'message'	=> 'You have to select an option',
			'rule'		=> array('naturalNumber')
		),
		'filename' => array(
			'extension' => array(
				'message'	=> 'This extension is invalid',
				'rule'		=> array('extension', array('gif', 'jpg', 'jpeg', 'png'))
			),
			'isUnique' => array(
				'message'	=> 'This value is already used',
				'rule'		=> 'isUnique'
			)
		),
		'description' => array(
			'allowEmpty'	=> TRUE,
			'message'		=> 'Must be at most %d chars',
			'rule'			=> array('maxLength', 255)
		)
	);

	/**
	 * belongsTo associations
	 * @var array
	 */
	public $belongsTo = array(
		'Album' => array(
			'className'		=> 'PhotosAlbum',
			'foreignKey'	=> 'album_id',
			'counterCache'	=> TRUE
		)
	);
	
	/**
	 * Called before each save operation, after validation. Return a non-true result to halt the save.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses Album::checkIfWritable() to check if the album is writeable
	 */
	public function beforeSave($options = array()) {
		//Checks if the album directory is writeable
		if(!empty($this->data[$this->alias]['album_id'])) {
			//Gets the album slug
			$slug = $this->Album->field('slug', array('id' => $this->data[$this->alias]['album_id']));
			return Album::checkIfWriteable($slug);
		}
		
		return TRUE;
	}
}