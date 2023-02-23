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
use Cake\Database\TypeFactory;
use Cake\Log\Log;
use MeCms\Database\Type\JsonEntityType;

require_once __DIR__ . DS . 'constants.php';

//Sets files to be copied
Configure::write('CONFIG_FILES', [
    ...Configure::read('CONFIG_FILES', []),
    'MeCms.recaptcha',
    'MeCms.me_cms',
    'MeCms.widgets',
]);

//Sets the directories to be created and which must be writable
Configure::write('WRITABLE_DIRS', [
    ...Configure::read('WRITABLE_DIRS', []),
    getConfigOrFail('Assets.target'),
    THUMBER_TARGET,
    UPLOADED,
    UPLOADED . '.trash',
    USER_PICTURES,
]);

//Sets symbolic links for vendor assets to be created
Configure::write('VENDOR_LINKS', [
    'studio-42' . DS . 'elfinder' => 'elfinder',
    'enyo' . DS . 'dropzone' . DS . 'dist' => 'dropzone',
] + Configure::read('VENDOR_LINKS', []));

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

//Adds all cache configurations
Configure::load('MeCms.cache');
foreach ((array)Configure::consume('Cache') as $key => $config) {
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
if (is_readable(CONFIG . 'recaptcha.php')) {
    Configure::load('recaptcha');
    if (!getConfig('RecaptchaMailhide.encryptKey')) {
        Configure::write('RecaptchaMailhide.encryptKey', getConfigOrFail('Recaptcha.private'));
    }
}

//Adds log for users actions
if (!Log::getConfig('users')) {
    Log::setConfig('users', [
        'className' => 'File',
        'path' => LOGS,
        'levels' => [],
        'file' => 'users.log',
        'scopes' => ['users'],
        'url' => env('LOG_DEBUG_URL'),
    ]);
}

require_once __DIR__ . DS . 'i18n_constants.php';
require_once __DIR__ . DS . 'requirements.php';

TypeFactory::map('jsonEntity', JsonEntityType::class);
