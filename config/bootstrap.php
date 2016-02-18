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

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;

/**
 * (here `\Cake\Core\Plugin` is used, as the plugins are not yet all loaded)
 */

//Loads the MeTools plugin
Plugin::load('MeTools', ['bootstrap' => TRUE, 'routes' => TRUE]);

//Loads the DatabaseBackup plugin
Plugin::load('DatabaseBackup', ['bootstrap' => TRUE]);

require_once 'constants.php';
require_once 'global_functions.php';

/**
 * MeCms configuration
 */
//Loads the configuration from the plugin
Configure::load('MeCms.me_cms');

$config = Configure::read('MeCms');

//Loads the configuration from the application, if exists
if(is_readable(CONFIG.'me_cms.php')) {
	Configure::load('me_cms', 'default', FALSE);
	
	Configure::write('MeCms', \Cake\Utility\Hash::mergeDiff(Configure::consume('MeCms'), $config));
}

//Fixes value
if(!is_int(Configure::read('MeCms.users.activation')) || Configure::read('MeCms.users.activation') > 2)
	Configure::write('MeCms.users.activation', 1);

//Forces debug and loads DebugKit on localhost, if required
if(is_localhost() && Configure::read('MeCms.main.debug_on_localhost') && !Configure::read('debug')) {
	Configure::write('debug', TRUE);
	
	Plugin::load('DebugKit', ['bootstrap' => TRUE]);
}

//Loads the theme
if(($theme = Configure::read('MeCms.frontend.theme')) && !Plugin::loaded($theme))
	Plugin::load($theme);

/**
 * Cache configuration
 */
//Loads the cache configuration from the plugin
Configure::load('MeCms.cache');

//Loads the cache from the application, if exists
if(is_readable(CONFIG.'cache.php'))
	Configure::load('cache', 'default', FALSE);

//Adds all cache configurations
foreach(Configure::consume('Cache') as $key => $config) {
	//Drops the default cache
	if($key === 'default')
		Cache::drop('default');
	
	Cache::config($key, $config);
}

/**
 * Widgets configuration
 */
//Loads the widgets configuration from the plugin
Configure::load('MeCms.widgets');

//Loads the widgets from the application, if exists
if(is_readable(CONFIG.'widgets.php'))
	Configure::load('widgets', 'default', FALSE);

//Adds the widgets configuration to the MeCms configuration
Configure::write('MeCms.frontend.widgets', Configure::consume('Widgets'));