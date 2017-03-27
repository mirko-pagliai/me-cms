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
namespace MeCms\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use MeCms\Controller\Component\LoginRecorderComponent;
use Reflection\ReflectionTrait;

/**
 * LoginRecorderTest class
 */
class LoginRecorderComponentTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \Cake\Controller\ComponentRegistry
     */
    protected $ComponentRegistry;

    /**
     * @var \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected $LoginRecorder;

    /**
     * Path of the log file
     * @var string
     */
    protected $log;

    /**
     * Internal method to get a `LoginRecorder` instance
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getLoginRecorderInstance()
    {
        $this->LoginRecorder = new LoginRecorderComponent($this->ComponentRegistry);
        $this->LoginRecorder->setUser(1);

        return $this->LoginRecorder;
    }

    /**
     * Internal method to get a `LoginRecorder` mock
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getLoginRecorderMock()
    {
        $this->LoginRecorder = $this->getMockBuilder(LoginRecorderComponent::class)
            ->setMethods(['getUserAgent'])
            ->setConstructorArgs([$this->ComponentRegistry])
            ->getMock();

        $this->LoginRecorder->method('getUserAgent')
            ->will($this->returnValue([
                'platform' => 'Linux',
                'browser' => 'Chrome',
                'version' => '55.0.2883.87',
            ]));

        $this->LoginRecorder->setUser(1);

        return $this->LoginRecorder;
    }

    /**
     * Internal method to get another `LoginRecorder` mock, different by the
     *  user agent
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getAnotherLoginRecorderMock()
    {
        $this->LoginRecorder = $this->getMockBuilder(LoginRecorderComponent::class)
            ->setMethods(['getUserAgent'])
            ->setConstructorArgs([$this->ComponentRegistry])
            ->getMock();

        $this->LoginRecorder->method('getUserAgent')
            ->will($this->returnValue([
                'platform' => 'Windows',
                'browser' => 'Firefox',
                'version' => '1.2.3',
            ]));

        $this->LoginRecorder->setUser(1);

        return $this->LoginRecorder;
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->log = LOGIN_RECORDS . 'user_1.log';

        $this->ComponentRegistry = new ComponentRegistry(new Controller);
        $this->LoginRecorder = $this->getLoginRecorderMock();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->LoginRecorder);

        //Deletes the file
        //@codingStandardsIgnoreLine
        @unlink($this->log);
    }

    /**
     * Test for `setUser()` method
     * @test
     */
    public function testSetUser()
    {
        $SerializedArray = $this->getProperty($this->LoginRecorder, 'SerializedArray');
        $this->assertInstanceOf('SerializedArray\SerializedArray', $SerializedArray);
        $this->assertEquals($this->log, $this->getProperty($SerializedArray, 'file'));

        //Sets again
        $this->getLoginRecorderInstance();
        $this->assertInstanceOf('MeCms\Controller\Component\LoginRecorderComponent', $this->LoginRecorder->setUser(1));
    }

    /**
     * Test for `setUser()` method, with invalid id
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Invalid value
     * @test
     */
    public function testSetUserInvalidId()
    {
        $this->LoginRecorder->setUser('string');
    }

    /**
     * Test for `getClientIp()` method
     * @test
     */
    public function testGetClientIp()
    {
        $this->getLoginRecorderInstance();
        $this->assertEmpty($this->invokeMethod($this->LoginRecorder, 'getClientIp'));
    }

    /**
     * Test for `getClientIp()` method on localhost
     * @test
     */
    public function testGetClientIpOnLocalhost()
    {
        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['clientIp'])
            ->getMock();

        $request->expects($this->once())
            ->method('clientIp')
            ->will($this->returnValue('::1'));

        $this->LoginRecorder->request = $request;

        $this->assertEquals('127.0.0.1', $this->invokeMethod($this->LoginRecorder, 'getClientIp'));
    }

    /**
     * Test for `getUserAgent()` method
     * @test
     */
    public function testGetUserAgent()
    {
        $result = $this->invokeMethod($this->LoginRecorder, 'getUserAgent');
        $this->assertEquals([
            'platform' => 'Linux',
            'browser' => 'Chrome',
            'version' => '55.0.2883.87',
        ], $result);

        $this->LoginRecorder = $this->getLoginRecorderInstance();

        $result = $this->invokeMethod($this->LoginRecorder, 'getUserAgent', [
            'Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0',
        ]);
        $this->assertEquals([
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '16.0',
        ], $result);
    }

    /**
     * Test for `read()` method
     * @test
     */
    public function testRead()
    {
        //For now is empty
        $result = $this->LoginRecorder->read();
        $this->assertEmpty($result);
        $this->assertTrue(is_array($result));

        $this->assertTrue($this->LoginRecorder->write());

        //After save, is not empty
        $result = $this->LoginRecorder->read();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));

        //Creates an empty file. Now is always empty
        file_put_contents($this->log, null);
        $result = $this->LoginRecorder->read();
        $this->assertEmpty($result);
        $this->assertTrue(is_array($result));
    }

    /**
     * Test for `read()` method, without the user ID
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage You must first set the user ID
     * @test
     */
    public function testReadMissingUserId()
    {
        $this->LoginRecorder = new LoginRecorderComponent($this->ComponentRegistry);
        $this->LoginRecorder->read();
    }

    /**
     * Test for `write()` method
     * @test
     */
    public function testWrite()
    {
        $this->assertTrue($this->LoginRecorder->write());

        $first = $this->LoginRecorder->read();
        $this->assertEquals(1, count($first));
        $this->assertInstanceOf('stdClass', $first[0]);
        $this->assertEquals(false, $first[0]->ip);
        $this->assertInstanceOf('Cake\I18n\Time', $first[0]->time);
        $this->assertEquals('Linux', $first[0]->platform);
        $this->assertEquals('Chrome', $first[0]->browser);
        $this->assertEquals('55.0.2883.87', $first[0]->version);
        $this->assertEquals(null, $first[0]->agent);

        sleep(1);

        //Calls again, as if the user had logged in again from the same client.
        //In this case, the previous record is deleted and a new one is written
        $this->assertTrue($this->LoginRecorder->write());

        $second = $this->LoginRecorder->read();
        $this->assertEquals(1, count($second));
        $this->assertInstanceOf('stdClass', $second[0]);
        $this->assertNotEquals($second, $first);

        sleep(1);

        //Calls again, with different user agent data, as if the user had logged
        //  in again, but from a different client. In this case, the previous
        //  record is not deleted
        $this->LoginRecorder = $this->getAnotherLoginRecorderMock();
        $this->assertTrue($this->LoginRecorder->write());

        $third = $this->LoginRecorder->read();
        $this->assertEquals(2, count($third));
        $this->assertEquals($second[0], $third[1]);
        $this->assertGreaterThan($third[1]->time, $third[0]->time);
    }

    /**
     * Test for `write()` method, without the user ID
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage You must first set the user ID
     * @test
     */
    public function testWriteMissingUserId()
    {
        $this->LoginRecorder = new LoginRecorderComponent($this->ComponentRegistry);
        $this->LoginRecorder->write();
    }
}
