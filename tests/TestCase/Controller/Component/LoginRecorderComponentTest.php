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

use Cake\Collection\Collection;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Http\ServerRequest;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeTools\TestSuite\ComponentTestCase;

/**
 * LoginRecorderTest class
 * @property \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $Component
 */
class LoginRecorderComponentTest extends ComponentTestCase
{
    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

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
            $Component->method('getUserAgent')->will($this->returnValue($userAgent ?: [
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
        $this->Component ??= $this->getMockForLoginRecorder();

        parent::setUp();
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
     * Test for `getUserAgent()` method
     * @test
     */
    public function testGetUserAgent(): void
    {
        $result = $this->invokeMethod($this->getMockForLoginRecorder(null), 'getUserAgent', ['Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0']);
        $this->assertSame([
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '16.0',
        ], $result);
    }

    /**
     * Test for `read()` method
     * @test
     */
    public function testRead(): void
    {
        //For now is empty
        $result = $this->Component->read();
        $this->assertInstanceof(Collection::class, $result);
        $this->assertTrue($result->isEmpty());

        //After save, is not empty
        $this->assertTrue($this->Component->write());
        $result = $this->Component->read();
        $this->assertInstanceof(Collection::class, $result);
        $this->assertFalse($result->isEmpty());

        //Without the user ID
        $this->expectException(InvalidPrimaryKeyException::class);
        $this->getMockForLoginRecorder()->setConfig('user', null)->read();
    }

    /**
     * Test for `write()` method
     * @test
     */
    public function testWrite(): void
    {
        $this->assertTrue($this->Component->write());

        $firstResult = $this->Component->read();
        $this->assertCount(1, $firstResult);
        $firstRow = $firstResult->first();
        $this->assertInstanceOf(Entity::class, $firstRow);
        $this->assertEquals(false, $firstRow->get('ip'));
        $this->assertInstanceOf(FrozenTime::class, $firstRow->get('time'));
        $this->assertEquals('Linux', $firstRow->get('platform'));
        $this->assertEquals('Chrome', $firstRow->get('browser'));
        $this->assertEquals('55.0.2883.87', $firstRow->get('version'));
        $this->assertEquals(null, $firstRow->get('agent'));

        sleep(1);

        //Calls again, as if the user had logged in again from the same client.
        //In this case, the previous record is deleted and a new one is written
        $this->assertTrue($this->Component->write());

        $secondResult = $this->Component->read();
        $this->assertCount(1, $secondResult);
        $this->assertInstanceOf(Entity::class, $secondResult->first());
        $this->assertNotEquals($secondResult->toList(), $firstResult->toList());

        sleep(1);

        //Calls again, with different user agent data, as if the user had logged
        //  in again, but from a different client. In this case, the previous
        //  record is not deleted
        $Component = $this->getMockForLoginRecorder(['getUserAgent'], [
            'platform' => 'Windows',
            'browser' => 'Firefox',
            'version' => '1.2.3',
        ]);
        $this->assertTrue($Component->write());

        $thirdResult = $Component->read();
        $this->assertCount(2, $thirdResult);
        $this->assertEquals($secondResult->first(), $thirdResult->take(1, 1)->first());
        $this->assertGreaterThan($thirdResult->take(1, 1)->first()->get('time'), $thirdResult->first()->get('time'));

        //Without the user ID
        $this->expectException(InvalidPrimaryKeyException::class);
        $this->getMockForLoginRecorder()->setConfig('user', null)->write();
    }
}
