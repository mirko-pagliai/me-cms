<?php
declare(strict_types=1);

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

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Type;
use Cake\Log\Log;
use EntityFileLog\Log\Engine\EntityFileLog;
use MeCms\Database\Type\JsonEntityType;

require_once __DIR__ . DS . 'constants.php';

//Sets directories to be created and must be writable
Configure::write('WRITABLE_DIRS', array_merge(Configure::read('WRITABLE_DIRS', []), [
    getConfig('Assets.target'),
    getConfigOrFail('DatabaseBackup.target'),
    BANNERS,
    LOGIN_RECORDS,
    PHOTOS,
    THUMBER_TARGET,
    UPLOADED,
    UPLOADED . '.trash',
    USER_PICTURES,
]));

//Sets symbolic links for vendor assets to be created
Configure::write('VENDOR_LINKS', array_merge(Configure::read('VENDOR_LINKS', []), [
    'npm-asset' . DS . 'js-cookie' . DS . 'src' => 'js-cookie',
    'sunhater' . DS . 'kcfinder' => 'kcfinder',
    'enyo' . DS . 'dropzone' . DS . 'dist' => 'dropzone',
]));

foreach (Configure::read('WRITABLE_DIRS') as $dir) {
    @mkdir($dir, 0777, true);

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

//Loads theme plugin
$theme = getConfig('default.theme');
if ($theme && !Plugin::loaded($theme)) {
    Plugin::load($theme);
}

//Adds all cache configurations
Configure::load('MeCms.cache');
foreach (Configure::consume('Cache') as $key => $config) {
    if (!Cache::getConfig($key)) {
        Cache::setConfig($key, $config);
    }
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

if (!getConfig('DatabaseBackup.mailSender')) {
    Configure::write('DatabaseBackup.mailSender', getConfigOrFail('MeCms.email.webmaster'));
}

//Adds log for users actions
if (!Log::getConfig('users')) {
    Log::setConfig('users', [
        'className' => EntityFileLog::class,
        'path' => LOGS,
        'levels' => [],
        'file' => 'users.log',
        'scopes' => ['users'],
        'url' => env('LOG_DEBUG_URL', null),
    ]);
}

require_once __DIR__ . DS . 'i18n_constants.php';

Type::map('jsonEntity', JsonEntityType::class);
