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
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Mailer\TransportFactory;
use Cake\TestSuite\Fixture\SchemaLoader;
use Cake\Utility\Security;
use MeCms\Mailer\Mailer;
use Migrations\TestSuite\Migrator;

ini_set('intl.default_locale', 'en_US');
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__) . DS);
define('VENDOR', ROOT . 'vendor' . DS);
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
define('CACHE', TMP . 'cache' . DS);
define('LOGS', TMP . 'log' . DS);
define('SESSIONS', TMP . 'sessions' . DS);
define('UPLOADED', WWW_ROOT . 'files' . DS);

foreach ([
    TMP . 'tests',
    LOGS,
    SESSIONS,
    CACHE . 'models',
    CACHE . 'persistent',
    CACHE . 'views',
] as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once CORE_PATH . 'config' . DS . 'bootstrap.php';
require_once ROOT . 'config' . DS . 'constants.php';

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
        'templates' => [APP . 'templates' . DS],
    ],
]);
/**
 * @todo remove as soon as possible
 */
Configure::write('Error.ignoredDeprecationPaths', '*/cakephp/cakephp/src/I18n/Time.php');
Configure::write('Session', ['defaults' => 'php']);
Configure::write('Assets.target', TMP . 'assets');
Configure::write('Tokens.usersClassOptions', ['foreignKey' => 'user_id', 'className' => 'Users']);
Configure::write('pluginsToLoad', ['Thumber/Cake', 'MeCms']);
Security::setSalt('a-long-but-not-random-value');
define('THUMBER_DRIVER', 'gd');

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

if (!getenv('db_dsn')) {
    putenv('db_dsn=mysql://travis@localhost/test?encoding=utf8&quoteIdentifiers=true');
    if (getenv('driver_test') == 'sqlite') {
        putenv('db_dsn=sqlite:///' . TMP . 'test.sq3');
    } elseif (getenv('driver_test') == 'postgres') {
        putenv('db_dsn=postgres://postgres@localhost/travis_ci_test');
    }
}
ConnectionManager::setConfig('test', ['url' => getenv('db_dsn')]);

Log::setConfig('debug', [
    'className' => 'File',
    'path' => LOGS,
    'levels' => ['notice', 'info', 'debug'],
    'file' => 'debug',
]);
Log::setConfig('error', [
    'className' => 'File',
    'path' => LOGS,
    'file' => 'error',
    'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
]);

TransportFactory::setConfig('debug', ['className' => 'Debug']);
Mailer::setConfig('default', ['transport' => 'debug', 'log' => true]);

$scheme = ConnectionManager::getConfigOrFail('test')['scheme'];

$migrator = new Migrator();
$migrator->run(['plugin' => 'MeCms']);
$loader = new SchemaLoader();
$loader->loadSqlFiles(TESTS . ($scheme == 'postgres' ? 'schema_postgres' : 'schema') . '.sql', 'test', false);

$_SERVER['PHP_SELF'] = '/';

echo 'Running tests for "' . $scheme . '" driver ' . PHP_EOL;
