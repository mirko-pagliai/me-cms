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
use Cake\Http\ServerRequest;
use Cake\I18n\FrozenTime;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeTools\TestSuite\ComponentTestCase;

/**
 * LoginRecorderTest class
 * @property \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $Component
 */
class LoginRecorderComponentTest extends ComponentTestCase
{
    protected const DEFAULT_USER_AGENT = [
        'platform' => 'Linux',
        'browser' => 'Chrome',
        'version' => '55.0.2883.87',
    ];

    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Internal method to get a `LoginRecorder` mock
     * @param string[] $methods Methods you want to mock
     * @param array<string, string> $userAgent Data returned by the `getUserAgent()` method
     * @return \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockForLoginRecorder(array $methods = ['getUserAgent'], array $userAgent = [])
    {
        /** @var \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $Component */
        $Component = $this->getMockForComponent(LoginRecorderComponent::class, $methods);

        if (in_array('getUserAgent', $methods)) {
            $Component->method('getUserAgent')->willReturn($userAgent ?: self::DEFAULT_USER_AGENT);
        }

        return $Component;
    }

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        $this->Component ??= $this->getMockForLoginRecorder();

        parent::setUp();
    }

    /**
     * Test for `getUserAgent()` method
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::getUserAgent()
     * @test
     */
    public function testGetUserAgent(): void
    {
        $result = $this->getMockForLoginRecorder([])->getUserAgent('Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0');
        $this->assertSame(['platform' => 'Windows', 'browser' => 'Firefox', 'version' => '16.0'], $result);
    }

    /**
     * Test for `getClientIp()` method
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::getClientIp()
     * @test
     */
    public function testGetClientIp(): void
    {
        $this->assertEmpty($this->Component->getClientIp());

        //On localhost
        $request = $this->getMockBuilder(ServerRequest::class)->onlyMethods(['clientIp'])->getMock();
        $request->method('clientIp')->willReturn('::1');
        $this->Component->getController()->setRequest($request);
        $this->assertEquals('127.0.0.1', $this->Component->getClientIp());
    }

    /**
     * Test for `read()` method
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::read()
     * @test
     */
    public function testRead(): void
    {
        $this->Component->setConfig('user', 1);

        //For now is empty
        $result = $this->Component->read();
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());

        //After save
        $this->assertTrue($this->Component->write());
        $result = $this->Component->read();
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    /**
     * Test for `read()` method, without the user id
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::read()
     * @test
     */
    public function testReadWithoutUserId(): void
    {
        $this->expectExceptionMessage('Expected configuration `user` not found.');
        $this->getMockForLoginRecorder()->read();
    }

    /**
     * Test for `write()` method
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::write()
     * @test
     */
    public function testWrite(): void
    {
        $this->Component->setConfig('user', 1);

        /**
         * First write.
         * Only one result is expected.
         */
        $expected = self::DEFAULT_USER_AGENT + ['agent' => null, 'ip' => '', 'time' => ''];
        $this->assertTrue($this->Component->write());

        $this->assertCount(1, $this->Component->read());
        $firstResultRow = $this->Component->read()->first();
        $this->assertInstanceOf(FrozenTime::class, $firstResultRow['time']);
        $this->assertEquals($expected, ['time' => ''] + $firstResultRow);

        /**
         * Second write.
         * The user had logged in from the same client, so the previous record is deleted and a new one is written.
         * Consequently, only one result is expected.
         */
        sleep(1);
        $this->assertTrue($this->Component->write());

        $this->assertCount(1, $this->Component->read());
        $secondResultRow = $this->Component->read()->first();
        $this->assertInstanceOf(FrozenTime::class, $secondResultRow['time']);
        $this->assertEquals($expected, ['time' => ''] + $secondResultRow);

        //`time` values are different
        $this->assertNotEquals($firstResultRow['time']->toUnixString(), $secondResultRow['time']->toUnixString());

        /**
         * Third write.
         * The user had logged in, but from a different client. So the previous record is NOT deleted.
         * Now two results are expected. The first is the one just added, the second is the one already present.
         */
        sleep(1);
        $userAgent = ['platform' => 'Windows', 'browser' => 'Firefox', 'version' => '1.2.3'];
        $Component = $this->getMockForLoginRecorder(['getUserAgent'], $userAgent)->setConfig('user', 1);
        $expected = $userAgent + ['agent' => null, 'ip' => '', 'time' => ''];
        $this->assertTrue($Component->write());

        $this->assertCount(2, $Component->read());
        $thirdResultFirstRow = $Component->read()->first();
        $this->assertInstanceOf(FrozenTime::class, $thirdResultFirstRow['time']);
        $this->assertEquals($expected, ['time' => ''] + $thirdResultFirstRow);

        $thirdResultLastRow = $Component->read()->last();
        $this->assertInstanceOf(FrozenTime::class, $thirdResultLastRow['time']);
        $thirdResultLastRow['time'] = $thirdResultLastRow['time']->toUnixString();
        $this->assertGreaterThan($thirdResultLastRow['time'], $thirdResultFirstRow['time']->toUnixString());

        //The last row the of third result is the second result row
        $secondResultRow['time'] = $secondResultRow['time']->toUnixString();
        $this->assertSame($thirdResultLastRow, $secondResultRow);
    }

    /**
     * Test for `write()` method, without the user id
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::write()
     * @test
     */
    public function testWriteWithoutUserId(): void
    {
        $this->expectExceptionMessage('Expected configuration `user` not found.');
        $this->getMockForLoginRecorder()->write();
    }
}
