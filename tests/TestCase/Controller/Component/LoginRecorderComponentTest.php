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

use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\TestSuite\ComponentTestCase;
use Tools\FileArray;

/**
 * LoginRecorderTest class
 */
class LoginRecorderComponentTest extends ComponentTestCase
{
    /**
     * Internal method to get a `LoginRecorder` instance
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getLoginRecorderInstance()
    {
        $component = $this->getMockForComponent(LoginRecorderComponent::class, null);
        $component->setConfig('user', 1);

        return $component;
    }

    /**
     * Internal method to get a `LoginRecorder` mock
     * @param array|null $userAgent Data returned by the `getUserAgent()` method
     * @return \MeCms\Controller\Component\LoginRecorderComponent
     */
    protected function getLoginRecorderMock($userAgent = [])
    {
        $component = $this->getMockForComponent(LoginRecorderComponent::class, ['getUserAgent']);
        $component->setConfig('user', 1);

        $component->method('getUserAgent')
            ->will($this->returnValue($userAgent ?: [
                'platform' => 'Linux',
                'browser' => 'Chrome',
                'version' => '55.0.2883.87',
            ]));

        return $component;
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        $this->Component = $this->getLoginRecorderMock();

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        //Deletes the file
        safe_unlink(LOGIN_RECORDS . 'user_1.log');

        parent::tearDown();
    }

    /**
     * Test for `getClientIp()` method
     * @test
     */
    public function testGetClientIp()
    {
        $LoginRecorderComponent = $this->getLoginRecorderInstance();
        $this->assertEmpty($this->invokeMethod($LoginRecorderComponent, 'getClientIp'));

        //On localhost
        $this->Component->request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['clientIp'])
            ->getMock();

        $this->Component->request->expects($this->once())
            ->method('clientIp')
            ->will($this->returnValue('::1'));

        $this->assertEquals('127.0.0.1', $this->invokeMethod($this->Component, 'getClientIp'));
    }

    /**
     * Test for `getFileArray()` method
     * @test
     */
    public function testGetFileArray()
    {
        $result = $this->Component->getFileArray();
        $this->assertInstanceOf(FileArray::class, $result);
        $this->assertEquals(LOGIN_RECORDS . 'user_1.log', $this->getProperty($result, 'filename'));
    }

    /**
     * Test for `getFileArray()` method, without the user ID
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have to set a valid user id
     * @test
     */
    public function testGetFileArrayMissingUserId()
    {
        $this->Component->setConfig('user', null);
        $this->Component->getFileArray();
    }

    /**
     * Test for `getFileArray()` method, with an invalid user ID
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have to set a valid user id
     * @test
     */
    public function testGetFileArrayInvalidUserId()
    {
        $this->Component->setConfig('user', 'string');
        $this->Component->getFileArray();
    }

    /**
     * Test for `getUserAgent()` method
     * @test
     */
    public function testGetUserAgent()
    {
        $result = $this->invokeMethod($this->Component, 'getUserAgent');
        $expected = [
            'platform' => 'Linux',
            'browser' => 'Chrome',
            'version' => '55.0.2883.87',
        ];
        $this->assertEquals($expected, $result);

        $component = $this->getLoginRecorderInstance();
        $expected = [
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '16.0',
        ];
        $result = $this->invokeMethod($component, 'getUserAgent', [
            'Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0',
        ]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for `read()` method
     * @test
     */
    public function testRead()
    {
        //For now is empty
        $result = $this->Component->read();
        $this->assertEmpty($result);
        $this->assertIsArray($result);

        $this->assertTrue($this->Component->write());

        //After save, is not empty
        $result = $this->Component->read();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        //Creates an empty file. Now is always empty
        safe_create_file(LOGIN_RECORDS . 'user_1.log');
        $result = $this->getLoginRecorderInstance()->read();
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
        $this->Component->setConfig('user', null)->read();
    }

    /**
     * Test for `write()` method
     * @test
     */
    public function testWrite()
    {
        $this->assertTrue($this->Component->write());

        $first = $this->Component->read();
        $this->assertEquals(1, count($first));
        $this->assertInstanceOf(Entity::class, $first[0]);
        $this->assertEquals(false, $first[0]->ip);
        $this->assertInstanceOf(Time::class, $first[0]->time);
        $this->assertEquals('Linux', $first[0]->platform);
        $this->assertEquals('Chrome', $first[0]->browser);
        $this->assertEquals('55.0.2883.87', $first[0]->version);
        $this->assertEquals(null, $first[0]->agent);

        sleep(1);

        //Calls again, as if the user had logged in again from the same client.
        //In this case, the previous record is deleted and a new one is written
        $this->assertTrue($this->Component->write());

        $second = $this->Component->read();
        $this->assertEquals(1, count($second));
        $this->assertInstanceOf(Entity::class, $second[0]);
        $this->assertNotEquals($second, $first);

        sleep(1);

        //Calls again, with different user agent data, as if the user had logged
        //  in again, but from a different client. In this case, the previous
        //  record is not deleted
        $component = $this->getLoginRecorderMock([
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '1.2.3',
        ]);
        $this->assertTrue($component->write());

        $third = $component->read();
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
        $this->Component->setConfig('user', null)->write();
    }
}
