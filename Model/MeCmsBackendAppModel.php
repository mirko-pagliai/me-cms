<?php

/**
 * MeCmsBackendAppModel
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

App::uses('MeToolsAppModel', 'MeTools.Model');
/**
 * Application level model.
 */
class MeCmsBackendAppModel extends MeToolsAppModel {
	/**
	 * Find methods
	 * @var array
	 */
    public $findMethods = array('random' =>  TRUE);
	
	/**
	 * "Random" search method. It searches random records
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
		
        return $results;
    }
}