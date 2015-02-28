<?php
/**
 * Post
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
 * Post Model
 */
class Post extends MeCmsAppModel {
	/**
	 * Display field
	 * @var string
	 */
	public $displayField = 'title';
	
	/**
	 * Order
	 * @var array 
	 */
	public $order = array('created' => 'DESC');

	/**
	 * Validation rules
	 * @var array
	 */
	public $validate = array(
		'id' => array(
			'blankOnCreate' => array(
				'message'	=> 'Can not be changed',
				'on'		=> 'create',
				'rule'		=> 'blank'
			)
		),
		'category_id' => array(
			'message'	=> 'You have to select an option',
			'rule'		=> array('naturalNumber')
		),
		'user_id' => array(
			'message'	=> 'You have to select an option',
			'rule'		=> array('naturalNumber')
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
			'between' => array(
				'last'		=> FALSE,
				'message'	=> 'Must be between %d and %d chars',
				'rule'		=> array('between', 6, 100)
			),
			'isValidSlug' => array(
				'last'		=> FALSE,
				'message'	=> 'Allowed chars: lowercase letters, numbers, dash',
				'rule'		=> array('isValidSlug')
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
	 * belongsTo associations
	 * @var array
	 */
	public $belongsTo = array(
		'Category' => array(
			'className'		=> 'MeCms.PostsCategory',
			'foreignKey'	=> 'category_id',
			'counterCache'	=> TRUE
		),
		'User' => array(
			'className'		=> 'MeCms.User',
			'foreignKey'	=> 'user_id',
			'counterCache'	=> TRUE
		)
	);
	
	/**
	 * Called after every deletion operation.
	 */
	public function afterDelete() {
		Cache::clearGroup('posts', 'posts');
	}
	
	/**
	 * Called after each find operation. Can be used to modify any results returned by find().
	 * @param mixed $results The results of the find operation
	 * @param boolean $primary Whether this model is being queried directly
	 * @return mixed Result of the find operation
	 */
	public function afterFind($results, $primary = FALSE) {
		foreach($results as $k => $v) {
			//If the text is not empty
			if(!empty($v[$this->alias]['text'])) {
				//Gets the first image
				preg_match('#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im', $v[$this->alias]['text'], $matches);
				
				if(!empty($matches[2]))
					$results[$k][$this->alias]['preview'] = Router::url($matches[2], TRUE);
			}
		}
		
		return $results;
	}
	
	/**
	 * Called after each successful save operation.
	 * @param boolean $created TRUE if this save created a new record
	 * @param array $options Options passed from Model::save()
	 */
	public function afterSave($created, $options = array()) {
		Cache::clearGroup('posts', 'posts');
	}
	
	/**
	 * Gets conditions from a filter form
	 * @param array $query Query (`$this->request->query`)
	 * @return array Conditions
	 */
	public function conditionsFromFilter($query = NULL) {
		$conditions = array();
		
		if(!empty($query['created'])) {
			$created = explode('-', $query['created']);
			
			//Sets the start and end dates
			//For the end date, see http://stackoverflow.com/a/1686742/1480263
			$start = sprintf('%d-%d-1', $created[0], $created[1]);
			$end = date('Y-m-t', strtotime(sprintf('%d-%d-1', $created[0], $created[1])));
						
			$conditions = array(
				'Post.created >=' => CakeTime::format($start, '%Y-%m-%d %H:%M'),
				'Post.created <=' => CakeTime::format($end, '%Y-%m-%d %H:%M')
			);
		}
		
		if(!empty($query['status'])) {
			if($query['status'] === 'active')
				$conditions['Post.active'] = TRUE;
			elseif($query['status'] === 'draft')
				$conditions['Post.active'] = FALSE;
		}
		
		$conditions = am(array_filter(array(
			'Post.title LIKE'	=> empty($query['title']) ? NULL : sprintf('%%%s%%', $query['title']),
			'user_id'			=> empty($query['user']) ? NULL : $query['user'],
			'category_id'		=> empty($query['category']) ? NULL : $query['category'],
			'priority'			=> empty($query['priority']) ? NULL : $query['priority']
		)), $conditions);
		
		return $conditions;
	}
}
