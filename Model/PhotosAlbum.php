<?php
/**
 * PhotosAlbum
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
App::uses('PhotoManager', 'MeCms.Utility');

/**
 * PhotosAlbum Model
 * @property Photo $Photo
 */
class PhotosAlbum extends MeCmsAppModel {
	/**
	 * Display field
	 * @var string
	 */
	public $displayField = 'title';

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
		'description' => array(
			'allowEmpty'	=> TRUE,
			'message'		=> 'Must be at most %d chars',
			'rule'			=> array('maxLength', 255)
		),
		'active' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> array('boolean')
		)
	);

	/**
	 * hasMany associations
	 * @var array
	 */
	public $hasMany = array(
		'Photo' => array(
			'className'		=> 'MeCms.Photo',
			'foreignKey'	=> 'album_id',
			'dependent'		=> FALSE
		)
	);
	
	/**
	 * "Active" find method. It finds for active records.
	 * @param string $state Either "before" or "after"
	 * @param array $query
	 * @param array $results
	 * @return mixed Query or results
	 */
	protected function _findActive($state, $query, $results = array()) {
        if($state === 'before') {			
			$query['conditions'] = empty($query['conditions']) ? array() : $query['conditions'];
			
			//Only active albums
			$query['conditions'][$this->alias.'.active'] = TRUE;
			//Only albums with photos
			$query['conditions'][$this->alias.'.photo_count >'] = 0;
			
            return $query;
        }
		
		if($query['limit'] === 1 && !empty($results[0]))
			return $results[0];
		
        return $results;
    }
	
	/**
	 * Called after every deletion operation.
	 */
	public function afterDelete() {
		Cache::clearGroup('photos', 'photos');
	}
	
	/**
	 * Called after each successful save operation.
	 * @param boolean $created TRUE if this save created a new record
	 * @param array $options Options passed from Model::save()
	 * @uses PhotoManager::createFolder()
	 */
	public function afterSave($created, $options = array()) {
		//Creates the album folder
		if($created)
			PhotoManager::createFolder($this->id);
		
		Cache::clearGroup('photos', 'photos');
	}
	
	/**
	 * Called before every deletion operation.
	 * @param boolean $cascade If TRUE records that depend on this record will also be deleted
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses PhotoManager::deleteFolder()
	 */
	public function beforeDelete($cascade = TRUE) {
		//Deletes the album folder
		return PhotoManager::deleteFolder($this->id);
	}
	
	/**
	 * Called before each save operation, after validation. Return a non-true result to halt the save.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses PhotoManager::folderIsWritable()
	 */
	public function beforeSave($options = array()) {
		parent::beforeSave($options);
		
		//Checks if the main folder is writable
		return PhotoManager::folderIsWritable();
	}
}