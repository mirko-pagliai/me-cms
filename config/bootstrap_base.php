<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

// (here `Cake\Core\Plugin` is used, as the plugins are not yet all loaded)
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Log\Log;
use Cake\Network\Request;

require_once __DIR__ . DS . 'constants.php';

//Loads MeTools plugins
if (!Plugin::loaded('MeTools')) {
    Plugin::load('MeTools', ['bootstrap' => true]);
}

foreach ([BANNERS, LOGIN_RECORDS, PHOTOS, UPLOADED] as $dir) {
    if (!file_exists($dir)) {
        //@codingStandardsIgnoreLine
        @mkdir($dir);
    }

    if (!is_writeable($dir)) {
        trigger_error(sprintf('Directory %s not writeable', $dir), E_USER_ERROR);
    }
}

//Sets configuration for the Tokens plugin
Configure::write('Tokens.usersClassOptions', [
    'foreignKey' => 'user_id',
    'className' => 'Users',
]);

//Loads the MeCms configuration and merges with the configuration from
//  application, if exists
Configure::load('MeCms.me_cms');

//
if (is_readable(CONFIG . 'me_cms.php')) {
    Configure::load('me_cms');
}

//Forces debug on localhost, if requiredì
if (!isset($request)) {
    $request = new Request;
}
if ($request->is('localhost') && getConfig('main.debug_on_localhost')) {
    Configure::write('debug', true);
}

//Loads theme plugin
$theme = getConfig('default.theme');

if ($theme && !Plugin::loaded($theme)) {
    Plugin::load($theme);
}

//Loads the cache configuration and merges with the configuration from
//  application, if exists
Configure::load('MeCms.cache');

if (is_readable(CONFIG . 'cache.php')) {
    Configure::load('cache');
}

//Adds all cache configurations
foreach (Configure::consume('Cache') as $key => $config) {
    //Drops cache configurations that already exist
    if (Cache::getConfig($key)) {
        Cache::drop($key);
    }

    Cache::setConfig($key, $config);
}

//Loads the banned IP configuration
if (is_readable(CONFIG . 'banned_ip.php')) {
    Configure::load('banned_ip');
}

//Loads the widgets configuration and merges with the configuration from
//  application, if exists
Configure::load('MeCms.widgets');

if (is_readable(CONFIG . 'widgets.php')) {
    Configure::load('widgets', 'default', false);
}

//Loads the reCAPTCHA configuration
Configure::load('recaptcha');

if (!getConfig('RecaptchaMailhide.encryptKey')) {
    Configure::write('RecaptchaMailhide.encryptKey', getConfigOrFail('Recaptcha.private'));
}

//Adds log for users actions
Log::setConfig('users', [
    'className' => 'MeCms\Log\Engine\SerializedLog',
    'path' => LOGS,
    'levels' => [],
    'file' => 'users.log',
    'scopes' => ['users'],
    'url' => env('LOG_DEBUG_URL', null),
]);
