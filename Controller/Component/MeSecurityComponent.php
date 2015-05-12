<?php
/**
 * MeSecurityComponent
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
 * @package		MeCms\Controller\Component
 * @see			SecurityComponent
 */

App::uses('SecurityComponent', 'Controller/Component');

/**
 * Creates an easy way to integrate tighter security in your application.
 * 
 * Rewrites `SecurityComponent` provided by CakePHP.
 */
class MeSecurityComponent extends SecurityComponent {
	/**
	 * Components
	 * @var array
	 */
	public $components = array('Session' => array('className' => 'MeTools.MeSession'));
	
	/**
     * Checks if the latest search has been executed out of the minimum interval
	 * @return bool
	 */
	public function checkLastSearch() {
        $interval = Configure::read('MeCms.security.search_interval');
		
        if(empty($interval))
            return TRUE;

        //If there was a previous search and if this was done before the minimum interval
        if($this->Session->read('lastSearch') && ($this->Session->read('lastSearch') + $interval) > time())
            return FALSE;

        //In any other case, saves the timestamp of the current search and returns TRUE
        $this->Session->write('lastSearch', time());
        return TRUE;
	}
}