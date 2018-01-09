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
use Cake\I18n\Date;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\Routing\DispatcherFactory;

$isCli = PHP_SAPI === 'cli';
$request = new Request;

require_once __DIR__ . DS . 'constants.php';

//Loads MeTools plugins
if (!Plugin::loaded('MeTools')) {
    Plugin::load('MeTools', ['bootstrap' => true]);
}

foreach ([BANNERS, LOGIN_RECORDS, PHOTOS, UPLOADED, USER_PICTURES] as $dir) {
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

if (is_readable(CONFIG . 'me_cms.php')) {
    Configure::load('me_cms');
}

//Forces debug on localhost, if required
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

//Loads other plugins
$pluginsToLoad = ['DatabaseBackup', 'Recaptcha', 'RecaptchaMailhide', 'Thumber', 'Tokens'];

foreach ($pluginsToLoad as $plugin) {
    if (!Plugin::loaded($plugin)) {
        Plugin::load($plugin, ['bootstrap' => true, 'routes' => true, 'ignoreMissing' => true]);
    }
}

if (!$isCli) {
    //Loads DebugKit, if debugging is enabled
    if (getConfig('debug') && !Plugin::loaded('DebugKit')) {
        Plugin::load('DebugKit', ['bootstrap' => true]);
    }

    Plugin::load('Gourmet/CommonMark');
    Plugin::load('WyriHaximus/MinifyHtml', ['bootstrap' => true]);
}

if (!getConfig(DATABASE_BACKUP . '.mailSender')) {
    Configure::write(DATABASE_BACKUP . '.mailSender', getConfigOrFail(ME_CMS . '.email.webmaster'));
}

//Sets the locale based on the current user
DispatcherFactory::add('LocaleSelector');

//Sets the default format used when type converting instances of this type to string
$format = getConfigOrFail('main.datetime.long');
Date::setToStringFormat($format);
FrozenDate::setToStringFormat($format);
FrozenTime::setToStringFormat($format);
Time::setToStringFormat($format);

require_once __DIR__ . DS . 'i18n_constants.php';
