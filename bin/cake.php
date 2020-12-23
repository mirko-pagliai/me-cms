#!/usr/bin/php -q
<?php
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

ob_start();
require_once dirname(__DIR__) . DS . 'tests' . DS . 'bootstrap.php';
ob_end_clean();

use App\Application;
use Cake\Console\CommandRunner;

// Build the runner with an application and root executable name.
$runner = new CommandRunner(new Application(dirname(__DIR__) . '/config'), 'cake');
exit($runner->run($argv));
