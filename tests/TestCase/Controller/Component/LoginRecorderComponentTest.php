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
namespace MeCms\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeTools\TestSuite\TestCase;

/**
 * LoginRecorderTest class
 */
class LoginRecorderComponentTest extends TestCase
{
    /**
     * @var \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected $LoginRecorder;

    /**
     * Internal method to get a `LoginRecorder` instance
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getLoginRecorderInstance()
    {
        $LoginRecorderComponent = new LoginRecorderComponent(new ComponentRegistry(new Controller));
        $LoginRecorderComponent->setConfig('user', 1);

        return $LoginRecorderComponent;
    }

    /**
     * Internal method to get a `LoginRecorder` mock
     * @param array|null $userAgent Data returned by the `getUserAgent()` method
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getLoginRecorderMock($userAgent = null)
    {
        $LoginRecorderComponent = $this->getMockBuilder(LoginRecorderComponent::class)
            ->setMethods(['getUserAgent'])
            ->setConstructorArgs([new ComponentRegistry(new Controller)])
            ->getMock();

        $LoginRecorderComponent->method('getUserAgent')
            ->will($this->returnValue($userAgent ?: [
                'platform' => 'Linux',
                'browser' => 'Chrome',
                'version' => '55.0.2883.87',
            ]));

        $LoginRecorderComponent->setConfig('user', 1);

        return $LoginRecorderComponent;
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

        $this->LoginRecorder = $this->getLoginRecorderMock();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes the file
        safe_unlink(LOGIN_RECORDS . 'user_1.log');
    }

    /**
     * Test for `getClientIp()` method
     * @test
     */
    public function testGetClientIp()
    {
        $LoginRecorderComponent = $this->getLoginRecorderInstance();
        $this->assertEmpty($this->invokeMethod($LoginRecorderComponent, 'getClientIp'));
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
     * Test for `getSerializedArray()` method
     * @test
     */
    public function testGetSerializedArray()
    {
        $result = $this->invokeMethod($this->LoginRecorder, 'getSerializedArray');
        $this->assertInstanceOf('SerializedArray\SerializedArray', $result);
        $this->assertEquals(LOGIN_RECORDS . 'user_1.log', $this->getProperty($result, 'file'));
    }

    /**
     * Test for `getSerializedArray()` method, without the user ID
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have to set a valid user id
     * @test
     */
    public function testGetSerializedArrayMissingUserId()
    {
        $this->LoginRecorder->setConfig('user', null);
        $this->invokeMethod($this->LoginRecorder, 'getSerializedArray');
    }

    /**
     * Test for `getSerializedArray()` method, with an invalid user ID
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have to set a valid user id
     * @test
     */
    public function testGetSerializedArrayInvalidUserId()
    {
        $this->LoginRecorder->setConfig('user', 'string');
        $this->invokeMethod($this->LoginRecorder, 'getSerializedArray');
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
        $this->assertIsArray($result);

        $this->assertTrue($this->LoginRecorder->write());

        //After save, is not empty
        $result = $this->LoginRecorder->read();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        //Creates an empty file. Now is always empty
        file_put_contents(LOGIN_RECORDS . 'user_1.log', null);
        $result = $this->LoginRecorder->read();
        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    /**
     * Test for `read()` method, without the user ID
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have to set a valid user id
     * @test
     */
    public function testReadMissingUserId()
    {
        $this->LoginRecorder->setConfig('user', null)->read();
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
        $this->LoginRecorder = $this->getLoginRecorderMock([
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '1.2.3',
        ]);
        $this->assertTrue($this->LoginRecorder->write());

        $third = $this->LoginRecorder->read();
        $this->assertEquals(2, count($third));
        $this->assertEquals($second[0], $third[1]);
        $this->assertGreaterThan($third[1]->time, $third[0]->time);
    }

    /**
     * Test for `write()` method, without the user ID
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have to set a valid user id
     * @test
     */
    public function testWriteMissingUserId()
    {
        $this->LoginRecorder->setConfig('user', null)->write();
    }
}
