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

/**
 * (here `Cake\Core\Plugin` is used, as the plugins are not yet all loaded)
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Log\Log;
use Cake\Network\Exception\InternalErrorException;
use MeTools\Network\Request;

require_once 'constants.php';
require_once 'global_functions.php';

/**
 * Loads MeTools plugins
 */
Plugin::load('MeTools', ['bootstrap' => TRUE]);

if(!is_writeable(BANNERS))
    throw new InternalErrorException(sprintf('File or directory %s not writeable', BANNERS));
		
if(!folder_is_writeable(PHOTOS))
    throw new InternalErrorException(sprintf('File or directory %s not writeable', PHOTOS));

/**
 * Loads the MeCms configuration
 */
Configure::load('MeCms.me_cms');

//Merges with the configuration from application, if exists
if(is_readable(CONFIG.'me_cms.php'))
	Configure::load('me_cms');

/**
 * Forces debug and loads DebugKit on localhost, if required
 */
if(is_localhost() && config('main.debug_on_localhost') && !config('debug')) {
	Configure::write('debug', TRUE);
	
    if(!Plugin::loaded('DebugKit'))
        Plugin::load('DebugKit', ['bootstrap' => TRUE]);
}

/**
 * Loads plugins
 */
Plugin::load('Assets', ['bootstrap' => TRUE]);
Plugin::load('Thumbs', ['bootstrap' => TRUE, 'routes' => TRUE]);
Plugin::load('DatabaseBackup', ['bootstrap' => TRUE]);

/**
 * Loads theme plugin
 */
$theme = config('frontend.theme');

if($theme && !Plugin::loaded($theme))
	Plugin::load($theme);

/**
 * Loads the cache configuration
 */
Configure::load('MeCms.cache');

//Merges with the configuration from application, if exists
if(is_readable(CONFIG.'cache.php'))
	Configure::load('cache');
    
//Adds all cache configurations
foreach(Configure::consume('Cache') as $key => $config) {
	//Drops cache configurations that already exist
	if(Cache::config($key))
		Cache::drop($key);
	
	Cache::config($key, $config);
}

/**
 * Loads the widgets configuration
 */
Configure::load('MeCms.widgets');

//Overwrites with the configuration from application, if exists
if(is_readable(CONFIG.'widgets.php'))
	Configure::load('widgets', 'default', FALSE);

//Adds log for users actions
Log::config('users', [
    'className' => 'MeCms\Log\Engine\SerializedLog',
    'path' => LOGS,
    'levels' => [],
    'file' => 'users.log',
    'scopes' => ['users'],
    'url' => env('LOG_DEBUG_URL', NULL),
]);

/**
 * Adds `isAdmin()` detector
 */
Request::addDetector('admin', function ($request) {
    return $request->param('prefix') === 'admin';
});

/**
 * Adds `isBanned()` detector.
 * It checks if the user's IP address is banned.
 */
Request::addDetector('banned', function ($request) {
    $banned = config('security.banned_ip');

    /**
     * The IP address is allowed if:
     *  - the list of banned ip is empty;
     *  - is localhost;
     *  - the IP address has already been verified.
     */
    if(!$banned || is_localhost() || $request->session()->read('allowed_ip'))
        return FALSE;
    
	//Replaces asteriskes
    $banned = preg_replace('/\\\\\*/', '[0-9]{1,3}', array_map('preg_quote', is_array($banned) ? $banned : [$banned]));

    if(preg_match(sprintf('/^(%s)$/', implode('|', $banned)), $request->clientIp())) {
        return TRUE;
    }
		
    //In any other case, saves the result in the session
    $request->session()->write('allowed_ip', TRUE);
    return FALSE;
});
