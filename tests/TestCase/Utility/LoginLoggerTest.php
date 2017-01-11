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
namespace MeCms\Test\TestCase\Utility;

use Cake\TestSuite\TestCase;
use MeCms\Utility\LoginLogger;
use Reflection\ReflectionTrait;

/**
 * LoginLoggerTest class
 */
class LoginLoggerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Utility\LoginLogger
     */
    protected $LoginLogger;

    /**
     * File path
     * @var string
     */
    protected $file;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->file = LOGIN_LOGS . 'user_1.log';

        $this->LoginLogger = $this->getMockBuilder(LoginLogger::class)
            ->setMethods(['_getUserAgent'])
            ->setConstructorArgs([1])
            ->getMock();

        $this->LoginLogger->method('_getUserAgent')
            ->will($this->returnValue([
                'platform' => 'Linux',
                'browser' => 'Chrome',
                'version' => '55.0.2883.87',
            ]));
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->LoginLogger);

        //Deletes the file
        //@codingStandardsIgnoreLine
        @unlink($this->file);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $SerializedArray = $this->getProperty($this->LoginLogger, 'SerializedArray');
        $this->assertEquals('SerializedArray\SerializedArray', get_class($SerializedArray));
        $this->assertEquals($this->file, $this->getProperty($SerializedArray, 'file'));
    }

    /**
     * Test for `read()` method
     * @test
     */
    public function testGet()
    {
        //For now is empty
        $result = $this->LoginLogger->read();
        $this->assertEmpty($result);
        $this->assertTrue(is_array($result));

        $this->assertTrue($this->LoginLogger->write());

        //After save, is not empty
        $result = $this->LoginLogger->read();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));

        //Creates an empty file. Now is always empty
        file_put_contents($this->file, null);
        $result = $this->LoginLogger->read();
        $this->assertEmpty($result);
        $this->assertTrue(is_array($result));
    }

    /**
     * Test for `write()` method
     * @test
     */
    public function testSave()
    {
        $this->assertTrue($this->LoginLogger->write());

        $first = $this->LoginLogger->read();
        $this->assertEquals(1, count($first));
        $this->assertEquals('stdClass', get_class($first[0]));
        $this->assertEquals(false, $first[0]->ip);
        $this->assertEquals('Cake\I18n\FrozenTime', get_class($first[0]->time));
        $this->assertEquals('Linux', $first[0]->platform);
        $this->assertEquals('Chrome', $first[0]->browser);
        $this->assertEquals('55.0.2883.87', $first[0]->version);
        $this->assertEquals(null, $first[0]->agent);

        sleep(1);

        //Calls again, as if the user had logged in again from the same client.
        //In this case, the previous record is deleted and a new one is written
        $this->assertTrue($this->LoginLogger->write());
        $second = $this->LoginLogger->read();
        $this->assertEquals(1, count($second));
        $this->assertEquals('stdClass', get_class($second[0]));
        $this->assertNotEquals($second, $first);

        //Calls again, with different user agent data, as if the user had logged
        //  in again, but from a different client. In this case, the previous
        //  record is not deleted
        $this->LoginLogger = $this->getMockBuilder(LoginLogger::class)
            ->setMethods(['_getUserAgent'])
            ->setConstructorArgs([1])
            ->getMock();

        $this->LoginLogger->method('_getUserAgent')
            ->will($this->returnValue([
                'platform' => 'Windows',
                'browser' => 'Firefox',
                'version' => '1.2.3',
            ]));

        sleep(1);

        $this->assertTrue($this->LoginLogger->write());
        $third = $this->LoginLogger->read();
        $this->assertEquals(2, count($third));
        $this->assertEquals($second[0], $third[1]);
        $this->assertGreaterThan($third[1]->time, $third[0]->time);
    }
}
