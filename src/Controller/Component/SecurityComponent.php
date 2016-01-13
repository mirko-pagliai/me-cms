<?php
/**
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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Component;

use MeTools\Controller\Component\SecurityComponent as BaseSecurityComponent;

/**
 * The Security Component creates an easy way to integrate tighter security in your application.
 */
class SecurityComponent extends BaseSecurityComponent {
	/**
     * Checks if the latest search has been executed out of the minimum interval
	 * @return bool
	 */
	public function checkLastSearch($query_id = NULL) {
        $interval = config('security.search_interval');
		
        if(empty($interval))
            return TRUE;
		
		$query_id = empty($query_id) ? NULL : md5($query_id);
		
		$last_search = $this->request->session()->read('last_search');
		
		if(!empty($last_search)) {
			//Checks if it's the same search
			if(!empty($query_id) && !empty($last_search['id']) && $query_id === $last_search['id'])
				return TRUE;
			//Checks if the interval has not yet expired
			elseif(($last_search['time'] + $interval) > time())
				return FALSE;
		}
		
		$this->request->session()->write('last_search', ['id' => $query_id, 'time' => time()]);
		
		return TRUE;
	}
}