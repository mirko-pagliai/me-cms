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

use Cake\Network\Request;

/**
 * Adds `isAdmin()` detector
 */
Request::addDetector('admin', function($request) {
    return $request->param('prefix') === 'admin';
});

/**
 * Adds `isBanned()` detector.
 * It checks if the user's IP address is banned.
 */
Request::addDetector('banned', function($request) {
    $banned = config('Banned');

    /**
     * The IP address is allowed if:
     *  - the list of banned IP is empty;
     *  - is localhost;
     *  - the IP address has already been verified.
     */
    if(!$banned || is_localhost() || $request->session()->read('allowed_ip')) {
        return FALSE;
    }
    
	//Replaces asteriskes
    $banned = preg_replace('/\\\\\*/', '[0-9]{1,3}', array_map('preg_quote', (array) $banned));

    if(preg_match(sprintf('/^(%s)$/', implode('|', $banned)), $request->clientIp())) {
        return TRUE;
    }
		
    //In any other case, saves the result in the session
    $request->session()->write('allowed_ip', TRUE);
    return FALSE;
});

/**
 * Adds `isOffline()` detector
 */
Request::addDetector('offline', function($request) {
    if(!config('default.offline')) {
        return FALSE;
    }

    //Always online for admin requests
    if($request->is('admin')) {
        return FALSE;
    }

    //Always online for some actions
    if($request->is('action', ['offline', 'login', 'logout'])) {
        return FALSE;
    }

    return TRUE;
});