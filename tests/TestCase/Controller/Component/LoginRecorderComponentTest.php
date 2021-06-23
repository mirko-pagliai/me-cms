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

namespace MeCms\Test\TestCase\Controller\Component;

use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use InvalidArgumentException;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeTools\TestSuite\ComponentTestCase;
use Tools\FileArray;
use Tools\Filesystem;

/**
 * LoginRecorderTest class
 * @property \MeCms\Controller\Component\LoginRecorderComponent $Component
 */
class LoginRecorderComponentTest extends ComponentTestCase
{
    /**
     * Internal method to get a `LoginRecorder` instance
     * @param array|null $methods Methods you want to mock
     * @param array $userAgent Data returned by the `getUserAgent()` method
     * @return \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockForLoginRecorder(?array $methods = ['getUserAgent'], array $userAgent = [])
    {
        /** @var \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $Component */
        $Component = $this->getMockForComponent(LoginRecorderComponent::class, $methods);

        if (is_array($methods) && in_array('getUserAgent', $methods)) {
            $Component->method('getUserAgent')
                ->will($this->returnValue($userAgent ?: [
                    'platform' => 'Linux',
                    'browser' => 'Chrome',
                    'version' => '55.0.2883.87',
                ]));
        }

        $Component->setConfig('user', 1);

        return $Component;
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        $this->Component = $this->Component ?: $this->getMockForLoginRecorder();

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->unlinkRecursive(LOGIN_RECORDS, false, true);
    }

    /**
     * Test for `getClientIp()` method
     * @test
     */
    public function testGetClientIp(): void
    {
        $this->assertEmpty($this->invokeMethod($this->Component, 'getClientIp'));

        //On localhost
        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['clientIp'])
            ->getMock();
        $request->expects($this->once())
            ->method('clientIp')
            ->will($this->returnValue('::1'));
        $this->Component->getController()->setRequest($request);
        $this->assertEquals('127.0.0.1', $this->invokeMethod($this->Component, 'getClientIp'));
    }

    /**
     * Test for `getFileArray()` method
     * @test
     */
    public function testGetFileArray(): void
    {
        $result = $this->Component->getFileArray();
        $this->assertInstanceOf(FileArray::class, $result);
        $this->assertEquals(LOGIN_RECORDS . 'user_1.log', $this->getProperty($result, 'filename'));

        //With invalid user ID
        foreach ([null, 'string'] as $value) {
            $this->assertException(function () use ($value) {
                $Component = $this->getMockForLoginRecorder();
                $Component->setConfig('user', $value);
                $Component->getFileArray();
            }, InvalidArgumentException::class, 'You have to set a valid user id');
        }
    }

    /**
     * Test for `getUserAgent()` method
     * @test
     */
    public function testGetUserAgent(): void
    {
        $expected = [
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '16.0',
        ];
        $Component = $this->getMockForLoginRecorder(['getUserAgent'], $expected);
        $result = $this->invokeMethod($Component, 'getUserAgent', ['Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0']);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for `read()` method
     * @test
     */
    public function testRead(): void
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
        (new Filesystem())->createFile(LOGIN_RECORDS . 'user_1.log');
        $result = $this->getMockForLoginRecorder()->read();
        $this->assertEmpty($result);
        $this->assertIsArray($result);

        //Without the user ID
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to set a valid user id');
        $this->getMockForLoginRecorder()->setConfig('user', false)->read();
    }

    /**
     * Test for `write()` method
     * @test
     */
    public function testWrite(): void
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
        $component = $this->getMockForLoginRecorder(['getUserAgent'], [
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '1.2.3',
        ]);
        $this->assertTrue($component->write());

        $third = $component->read();
        $this->assertEquals(2, count($third));
        $this->assertEquals($second[0], $third[1]);
        $this->assertGreaterThan($third[1]->time, $third[0]->time);

        //Without the user ID
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to set a valid user id');
        $this->getMockForLoginRecorder()->setConfig('user', null)->write();
    }
}
