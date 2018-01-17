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
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Routing\DispatcherFactory;

ini_set('intl.default_locale', 'en_US');
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

require dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Path constants to a few helpful things.
define('ROOT', dirname(__DIR__) . DS);
define('VENDOR', ROOT . 'vendor' . DS);
define('CAKE_CORE_INCLUDE_PATH', VENDOR . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', VENDOR . 'cakephp' . DS . 'cakephp' . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('TESTS', ROOT . 'tests' . DS);
define('TEST_APP', TESTS . 'test_app' . DS);
define('APP', TEST_APP . 'TestApp' . DS);
define('APP_DIR', 'TestApp');
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', APP . 'webroot' . DS);
define('TMP', sys_get_temp_dir() . DS . 'me_cms' . DS);
define('CONFIG', APP . 'config' . DS);
define('CACHE', TMP);
define('LOGS', TMP . 'cakephp_log' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

//@codingStandardsIgnoreStart
@mkdir(LOGS);
@mkdir(SESSIONS);
@mkdir(CACHE);
@mkdir(CACHE . 'views');
@mkdir(CACHE . 'models');
//@codingStandardsIgnoreEnd

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => APP_DIR,
    'webroot' => 'webroot',
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'plugins' => [APP . 'Plugin' . DS],
        'templates' => [
            APP . 'Template' . DS,
            ROOT . 'src' . DS . 'Template' . DS,
            ],
    ],
]);

Cache::setConfig([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true,
    ],
    '_cake_model_' => [
        'engine' => 'File',
        'prefix' => 'cake_model_',
        'serialize' => true,
    ],
    'default' => [
        'engine' => 'File',
        'prefix' => 'default_',
        'serialize' => true,
    ],
]);

// Ensure default test connection is defined
ConnectionManager::setConfig('test', ['url' => 'mysql://root@localhost/test']);

Configure::write('Session', ['defaults' => 'php']);

/**
 * Loads plugins
 */
Plugin::load('Assets', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'assets' . DS,
]);

Configure::write('DatabaseBackup.connection', 'test');
Configure::write('DatabaseBackup.target', TMP . 'backups');

Plugin::load('DatabaseBackup', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'cakephp-database-backup' . DS,
]);

Plugin::load('Recaptcha', [
    'path' => VENDOR . 'crabstudio' . DS . 'recaptcha' . DS,
]);

Plugin::load('RecaptchaMailhide', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'cakephp-recaptcha-mailhide' . DS,
    'routes' => true,
]);

Configure::write('Tokens.usersClassOptions', [
    'foreignKey' => 'user_id',
    'className' => 'Users',
]);

Plugin::load('Tokens', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'cakephp-tokens' . DS,
]);

Plugin::load('Thumber', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'cakephp-thumber' . DS,
    'routes' => true,
]);

//This adds `apache_get_modules()` and `apache_get_version()` functions
require 'apache_functions.php';

Plugin::load('MeTools', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'me-tools' . DS,
]);

define('UPLOADED', WWW_ROOT . 'files' . DS);
define('LOGIN_RECORDS', TMP . 'login' . DS);

Plugin::load('MeCms', ['bootstrap' => true, 'path' => ROOT, 'routes' => true]);

require CORE_PATH . 'config' . DS . 'bootstrap.php';

//Sets debug log
Log::config('debug', [
    'className' => 'File',
    'path' => LOGS,
    'levels' => ['notice', 'info', 'debug'],
    'file' => 'debug',
]);

DispatcherFactory::add('Routing');
DispatcherFactory::add('ControllerFactory');

Email::setConfigTransport('debug', ['className' => 'Debug']);
Email::setConfig('default', ['transport' => 'debug', 'log' => true]);

Configure::write(DATABASE_BACKUP . '.mailSender', getConfigOrFail('email.webmaster'));

//This makes it believe that KCFinder is installed
$kcfinder = KCFINDER . 'browse.php';
//@codingStandardsIgnoreLine
@mkdir(dirname($kcfinder));
file_put_contents($kcfinder, '@version 3.12');
