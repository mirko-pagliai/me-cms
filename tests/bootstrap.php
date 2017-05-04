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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Email;
use Cake\Routing\DispatcherFactory;

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
define('TMP', sys_get_temp_dir() . DS);
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

require CORE_PATH . 'config' . DS . 'bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

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
        'templates' => [APP . 'Template' . DS],
    ]
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
if (!getenv('db_dsn')) {
    putenv('db_dsn=mysql://travis@localhost/test');
}

ConnectionManager::setConfig('test', ['url' => getenv('db_dsn')]);
ConnectionManager::setConfig('test_custom_i18n_datasource', ['url' => getenv('db_dsn')]);

Configure::write('Session', [
    'defaults' => 'php'
]);

/**
 * Loads plugins
 */
Configure::write('Assets.target', TMP . 'assets');

//@codingStandardsIgnoreLine
@mkdir(Configure::read('Assets.target'));

Plugin::load('Assets', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'assets' . DS,
]);

Configure::write('MysqlBackup.connection', 'test');
Configure::write('MysqlBackup.target', TMP . 'backups');

//@codingStandardsIgnoreLine
@mkdir(Configure::read('MysqlBackup.target'));

Plugin::load('MysqlBackup', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'cakephp-mysql-backup' . DS,
]);

Configure::write('Thumbs.target', TMP . 'thumbs');

//@codingStandardsIgnoreLine
@mkdir(Configure::read('Thumbs.target'));

Plugin::load('Thumber', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'cakephp-thumber' . DS,
    'routes' => true,
]);

Plugin::load('MeTools', [
    'bootstrap' => true,
    'path' => VENDOR . 'mirko-pagliai' . DS . 'me-tools' . DS,
]);

define('UPLOADED', WWW_ROOT . 'files' . DS);
define('LOGIN_RECORDS', TMP . 'login' . DS);

//@codingStandardsIgnoreStart
@mkdir(LOGIN_RECORDS);
@mkdir(UPLOADED);
//@codingStandardsIgnoreEnd

Plugin::load('MeCms', [
    'bootstrap' => false, //Doesn't load the bootstrap
    'path' => ROOT,
    'routes' => true,
]);

require_once ROOT . 'config' . DS . 'bootstrap_base.php';

DispatcherFactory::add('Routing');
DispatcherFactory::add('ControllerFactory');

Email::setConfigTransport('debug', ['className' => 'Debug']);
Email::setConfig('default', ['transport' => 'debug', 'log' => true]);

ini_set('intl.default_locale', 'en_US');
