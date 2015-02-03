<?php
/**
 * Banner
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
App::uses('BannerManager', 'MeCms.Utility');

/**
 * Banner Model
 */
class Banner extends MeCmsAppModel {
	/**
	 * Display field
	 * @var string
	 */
	public $displayField = 'filename';
	
	/**
	 * Order
	 * @var array 
	 */
	public $order = array('filename' => 'ASC');
	
	/**
	 * Validation rules
	 * @var array
	 */
	public $validate = array(
		'id' => array(
			'blankOnCreate' => array(
				'on'	=> 'create',
				'rule'	=> 'blank'
			)
		),
		'position_id' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> 'naturalnumber'
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
			'maxLength' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be at most %d chars',
				'rule'		=> array('maxLength', 255)
			),
			'blankonUpdate' => array(
				'on'	=> 'update',
				'rule'	=> 'blank'
			)
		),
		'target' => array(
			'maxLength' => array(
				'allowEmpty'	=> TRUE,
				'last'			=> FALSE,
				'message'		=> 'Must be at most %d chars',
				'rule'			=> array('maxLength', 255)
			),
			'url' => array(
				'message'	=> 'Must be a valid url',
				'rule'		=> array('url', TRUE)
			)
		),
		'description' => array(
			'allowEmpty'	=> TRUE,
			'message'		=> 'Must be at most %d chars',
			'rule'			=> array('maxLength', 255)
		),
		'active' => array(
			'message'	=> 'You have to select a valid option',
			'rule'		=> 'boolean'
		)
	);

	/**
	 * belongsTo associations
	 * @var array
	 */
	public $belongsTo = array(
		'Position' => array(
			'className'		=> 'MeCms.BannersPosition',
			'foreignKey'	=> 'position_id',
			'counterCache'	=> TRUE
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
			
			//Only active items
			$query['conditions'][$this->alias.'.active'] = TRUE;
			
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
		Cache::clearGroup('banners', 'banners');
	}
	
	/**
	 * Called after each find operation. Can be used to modify any results returned by find().
	 * @param mixed $results The results of the find operation
	 * @param boolean $primary Whether this model is being queried directly
	 * @return mixed Result of the find operation
	 * @uses BannerManager::getPath()
	 * @uses BannerManager::getUrl()
	 */
	public function afterFind($results, $primary = FALSE) {
		foreach($results as $k => $v) {
			//If the filename is available, adds the file url
			if(!empty($v['filename'])) {
				$results[$k]['path'] = BannerManager::getPath($v['filename']);
				$results[$k]['url'] = BannerManager::getUrl($v['filename']);
			}
			elseif(!empty($v[$this->alias]['filename'])) {
				$results[$k][$this->alias]['path'] = BannerManager::getPath($v[$this->alias]['filename']);
				$results[$k][$this->alias]['url'] = BannerManager::getUrl($v[$this->alias]['filename']);
			}
		}
		
		return $results;
	}
	
	/**
	 * Called after each successful save operation.
	 * @param boolean $created TRUE if this save created a new record
	 * @param array $options Options passed from Model::save()
	 * @uses BannerManager::save()
	 */
	public function afterSave($created, $options = array()) {
		//Saves the file
		if($created)
			BannerManager::save($this->data[$this->alias]['filename']);
		
		Cache::clearGroup('banners', 'banners');
	}
	
	/**
	 * Called before every deletion operation.
	 * @param boolean $cascade If TRUE records that depend on this record will also be deleted
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses BannerManager::delete()
	 */
	public function beforeDelete($cascade = TRUE) {
		//Gets the banner
		$banner = $this->find('first', array(
			'conditions'	=> array('id' => $this->id),
			'fields'		=> array('filename')
		));
		
		//Deletes the banner and returns
		return BannerManager::delete($banner['Banner']['filename']);
	}
	
	/**
	 * Called before each save operation, after validation. Return a non-true result to halt the save.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 * @uses BannerManager::folderIsWritable
	 */
	public function beforeSave($options = array()) {
		parent::beforeSave($options);
		
		//Checks if the folder is writeable
		return BannerManager::folderIsWritable();
	}
}