<?php
/**
 * MeCmsAppModel
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

App::uses('AppModel', 'Model');

/**
 * Application level model.
 */
class MeCmsAppModel extends AppModel {
	/**
	 * Behaviors
	 * @var array 
	 */
	public $actsAs = array('Containable');
	
	/**
	 * Find methods
	 * @var array
	 */
    public $findMethods = array('active' => TRUE, 'random' =>  TRUE);
	
	/**
	 * Recursive level
	 * @var int
	 */
	public $recursive = -1;
	
	/**
	 * Validation domain
	 * @var string
	 */
    public $validationDomain = 'validation_me_cms';
	
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
			//Only items published in the past
			$query['conditions'][$this->alias.'.created <='] = date('Y-m-d H:i:s');
			
            return $query;
        }
		
		if($query['limit'] === 1 && !empty($results[0]))
			return $results[0];
		
        return $results;
    }
	
	/**
	 * "Random" find method. It finds random records.
	 * @param string $state Either "before" or "after"
	 * @param array $query
	 * @param array $results
	 * @return mixed Query or results
	 */
	protected function _findRandom($state, $query, $results = array()) {
        if($state === 'before') {
			//If not specified, the limit is '1'
			$query['limit'] = empty($query['limit']) ? 1 : $query['limit'];
			//Order
			$query['order']	= 'rand()';
			
            return $query;
        }
		
		if($query['limit'] === 1 && !empty($results[0]))
			return $results[0];
		
        return $results;
    }
	
	/**
	 * Called before each save operation, after validation. Return a non-true result to halt the save.
	 * @param array $options Options passed from Model::save()
	 * @return boolean TRUE if the operation should continue, FALSE if it should abort
	 */
	public function beforeSave($options = array()) {		
		//If the creation datetime isn't set but the field exists, then it is the current datetime
		if(isset($this->data[$this->alias]['created']) && empty($this->data[$this->alias]['created']))
			$this->data[$this->alias]['created'] = CakeTime::format(time(), '%Y-%m-%d %H:%M:%S');
		
		return TRUE;
	}
	
	/**
	 * Checks whether an object belongs to a user.
	 * @param int $id Object id
	 * @param int $user_id User id
	 * @return bool TRUE if it belongs to the user, otherwise FALSE
	 */
	public function isOwnedBy($id, $user_id) {
		return $this->field('id', compact('id', 'user_id')) !== FALSE;
	}
}