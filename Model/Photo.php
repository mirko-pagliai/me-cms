<?php
/**
 * Photo
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Model
 */

App::uses('MeCmsAppModel', 'MeCms.Model');
App::uses('PhotoManager', 'MeCms.Utility');

/**
 * Photo Model
 */
class Photo extends MeCmsAppModel {
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
				'last'		=> FALSE,
				'message'	=> 'This extension is invalid',
				'rule'		=> array('extension', array('gif', 'jpg', 'jpeg', 'png'))
			),
			'isUnique' => array(
				'last'		=> FALSE,
				'message'	=> 'This value is already used',
				'rule'		=> 'isUnique'
			),
			'blank' => array(
				//Blank on update
				'on'	=> 'update',
				'rule'	=> 'blank'
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
	 * Called after each find operation. Can be used to modify any results returned by find().
	 * @param mixed $results The results of the find operation
	 * @param boolean $primary Whether this model is being queried directly
	 * @return mixed Result of the find operation
	 * @uses PhotoManager::getPath()
	 */
	public function afterFind($results, $primary = FALSE) {
		foreach($results as $k => $v) {
			//If the file name and the album ID are available, adds the file path
			if(!empty($v['filename']) && !empty($v['album_id']))
				$results[$k]['path'] = PhotoManager::getPath($v['filename'], $v['album_id']);
			elseif(!empty($v[$this->alias]['filename']) && !empty($v[$this->alias]['album_id']))
				$results[$k][$this->alias]['path'] = PhotoManager::getPath($v[$this->alias]['filename'], $v[$this->alias]['album_id']);
		}
		
		return $results;
	}
	
	/**
	 * Called after each successful save operation.
	 * @param boolean $created TRUE if this save created a new record
	 * @param array $options Options passed from Model::save()
	 * @uses PhotoManager::save()
	 */
	public function afterSave($created, $options = array()) {
		//Saves the photos
		if($created)
			PhotoManager::save($this->data[$this->alias]['filename'], $this->data[$this->alias]['album_id']);
		
		Cache::clearGroup('photos', 'photos');
	}
	
	/**
	 * Called before every deletion operation.
	 * @param boolean $cascade If TRUE records that depend on this record will also be deleted
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses PhotoManager::delete()
	 */
	public function beforeDelete($cascade = TRUE) {
		//Gets the photo
		$photo = $this->find('first', array(
			'conditions'	=> array('id' => $this->id),
			'fields'		=> array('album_id', 'filename')
		));
		
		//Deletes the photo
		return PhotoManager::delete($photo['Photo']['filename'], $photo['Photo']['album_id']);
	}
	
	/**
	 * Called before each save operation, after validation. Return a non-true result to halt the save.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses PhotoManager::folderIsWritable()
	 */
	public function beforeSave($options = array()) {
		//Checks if the album folder is writable
		if(!empty($this->data[$this->alias]['album_id']))
			return PhotoManager::folderIsWritable($this->data[$this->alias]['album_id']);
		
		return TRUE;
	}
}