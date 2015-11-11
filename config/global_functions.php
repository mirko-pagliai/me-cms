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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */

use Cake\Core\Configure;

if(!function_exists('config')) {
	/**
	 * Gets config values stored in the configuration.
	 * It will first look in the MeCms configuration, then in the application configuration
	 * @param string|NULL $key Configuration key
	 * @return mixed Configuration value
	 */
	function config($key = NULL) {		
		if($key !== NULL && Configure::check(sprintf('MeCms.%s', $key)))
			return Configure::read(sprintf('MeCms.%s', $key));
		
		return Configure::read($key);
	}
}

if(!function_exists('is_localhost')) {
	/**
	 * Checks if is localhost
	 * @return bool
	 */
    function is_localhost() {
		return in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
	}
}