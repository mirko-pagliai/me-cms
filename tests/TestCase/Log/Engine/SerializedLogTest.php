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
namespace MeCms\Test\TestCase\Log\Engine;

use Cake\Log\Log;
use MeCms\Log\Engine\SerializedLog;
use MeTools\TestSuite\TestCase;

/**
 * SerializedLogTest class
 */
class SerializedLogTest extends TestCase
{
    /**
     * Internal method to write some logs
     */
    protected function writeSomeLogs()
    {
        Log::write('error', 'This is an error message');
        Log::write('critical', 'This is a critical message');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Log::drop('error');
    }

    /**
     * Test for `getLogAsObject()` method
     * @test
     */
    public function testGetLogAsObject()
    {
        $object = new SerializedLog;

        $getLogAsObjectMethod = function ($level, $message) use ($object) {
            return $this->invokeMethod($object, 'getLogAsObject', [$level, $message]);
        };

        $trace = '#0 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/ControllerFactory.php(72): Cake\Http\ControllerFactory->missingController(Object(Cake\Network\Request))
#1 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/ActionDispatcher.php(92): Cake\Http\ControllerFactory->create(Object(Cake\Network\Request), Object(Cake\Network\Response))
#2 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/BaseApplication.php(83): Cake\Http\ActionDispatcher->dispatch(Object(Cake\Network\Request), Object(Cake\Network\Response))
#3 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Http\BaseApplication->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#4 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Routing/Middleware/RoutingMiddleware.php(62): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#5 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Routing\Middleware\RoutingMiddleware->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#6 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Routing/Middleware/AssetMiddleware.php(88): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#7 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Routing\Middleware\AssetMiddleware->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#8 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Error/Middleware/ErrorHandlerMiddleware.php(81): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#9 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Error\Middleware\ErrorHandlerMiddleware->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#10 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(51): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#11 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Server.php(90): Cake\Http\Runner->run(Object(Cake\Http\MiddlewareQueue), Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#12 /home/mirko/Server/mirkopagliai/webroot/index.php(37): Cake\Http\Server->run()
#13 {main}';

        $result = $getLogAsObjectMethod('error', 'example of message');
        $this->assertTrue($result->has(['level', 'datetime', 'message', 'full']));
        $this->assertEquals('error', $result->level);
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->datetime);
        $this->assertEquals('example of message', $result->message);

        $message = file_get_contents(TEST_APP . 'examples' . DS . 'stacktraces' . DS . 'example1');
        $result = $getLogAsObjectMethod('error', $message);
        $this->assertTrue($result->has(['level', 'datetime', 'exception', 'message', 'request', 'ip', 'trace', 'full']));
        $this->assertEquals('error', $result->level);
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->datetime);
        $this->assertEquals('Cake\Routing\Exception\MissingControllerException', $result->exception);
        $this->assertEquals('Controller class NoExistingRoute could not be found.', $result->message);
        $this->assertEquals('/noExistingRoute', $result->request);
        $this->assertEquals('1.1.1.1', $result->ip);
        $this->assertEquals($trace, $result->trace);

        $message = file_get_contents(TEST_APP . 'examples' . DS . 'stacktraces' . DS . 'example2');
        $result = $getLogAsObjectMethod('error', $message);
        $this->assertTrue($result->has([
            'level',
            'datetime',
            'exception',
            'message',
            'attributes',
            'request',
            'referer',
            'ip',
            'trace',
            'full',
        ]));
        $this->assertEquals('error', $result->level);
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->datetime);
        $this->assertEquals('Cake\Routing\Exception\MissingControllerException', $result->exception);
        $this->assertEquals('Controller class NoExistingRoute could not be found.', $result->message);
        $this->assertEquals('array (' . PHP_EOL .
            '  \'class\' => \'NoExistingRoute\',' . PHP_EOL .
            '  \'plugin\' => false,' . PHP_EOL .
            '  \'prefix\' => false,' . PHP_EOL .
            '  \'_ext\' => false,' . PHP_EOL .
            ')', $result->attributes);
        $this->assertEquals('/noExistingRoute', $result->request);
        $this->assertEquals('/noExistingReferer', $result->referer);
        $this->assertEquals('1.1.1.1', $result->ip);
        $this->assertEquals($trace, $result->trace);
    }

    /**
     * Test for `log()` method
     * @test
     */
    public function testLog()
    {
        $config = [
            'className' => 'MeCms\Log\Engine\SerializedLog',
            'path' => LOGS,
            'file' => 'error',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
            'url' => env('LOG_ERROR_URL', null),
        ];

        Log::setConfig('error', $config);

        //Writes some logs
        $this->writeSomeLogs();

        $this->assertLogContains('Error: This is an error message', 'error');
        $this->assertLogContains('Critical: This is a critical message', 'error');

        //Tests the serialized log is not empty
        $logs = safe_unserialize(file_get_contents(LOGS . 'error_serialized.log'));
        $this->assertNotEmpty($logs);

        foreach ($logs as $log) {
            $this->assertInstanceOf('Cake\ORM\Entity', $log);
            $this->assertContains($log->level, ['critical', 'error']);
            $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $log->datetime);
            $this->assertRegExp('/^This is (a critical|an error) message$/', $log->message);
            $this->assertRegExp('/^[\d-:\s]{19} (Critical|Error)/', $log->full);
        }

        //Checks for fileperms
        $this->assertFilePerms(LOGS . 'error.log', ['0644', '0664']);
        $this->assertFilePerms(LOGS . 'error_serialized.log', ['0644', '0664']);

        //Deletes all logs, drops and reconfigure, adding `mask`
        safe_unlink_recursive(LOGS);
        Log::drop('error');
        Log::setConfig('error', array_merge($config, ['mask' => 0777]));

        //Writes some logs
        $this->writeSomeLogs();

        $this->assertLogContains('Error: This is an error message', 'error');
        $this->assertLogContains('Critical: This is a critical message', 'error');

        //Tests the serialized log is not empty
        $logs = safe_unserialize(file_get_contents(LOGS . 'error_serialized.log'));
        $this->assertNotEmpty($logs);

        //Checks for fileperms
        $this->assertFilePerms(LOGS . 'error.log', '0777');
        $this->assertFilePerms(LOGS . 'error_serialized.log', '0777');
    }
}
