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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Model
 */

App::uses('MeToolsAppModel', 'MeTools.Model');

/**
 * Application level model.
 */
class MeCmsAppModel extends MeToolsAppModel {
	/**
	 * Find methods
	 * @var array
	 */
    public $findMethods = array('active' => TRUE, 'random' =>  TRUE);
	
	/**
	 * "Active" find method. It finds for active records.
	 * @param string $state Either "before" or "after"
	 * @param array $query
	 * @param array $results
	 * @return mixed Query or results
	 */
	protected function _findActive($state, $query, $results = array()) {
        if($state === 'before') {
			//If not specified, the limit is '1'
			$query['limit'] = empty($query['limit']) ? 1 : $query['limit'];
			
			$query['conditions'] = empty($query['conditions']) ? array() : $query['conditions'];
			
			//Only active items
			$query['conditions'][$this->alias.'.active'] = TRUE;
			//Only items published in the past
			$query['conditions'][$this->alias.'.created <='] = date('Y-m-d H:i:s');
			
            return $query;
        }
		
		if($query['limit'] < 2 && !empty($results[0]))
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
		
		if($query['limit'] < 2 && !empty($results[0]))
			return $results[0];
		
        return $results;
    }
}