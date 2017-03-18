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
namespace MeCms\Test\TestCase\Controller\Traits;

use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use MeCms\Controller\Traits\DownloadTrait;
use Reflection\ReflectionTrait;

/**
 * ExampleController class
 */
class ExampleController extends Controller
{
    use DownloadTrait;
}

/**
 * DownloadTraitTest class
 */
class DownloadTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var ExampleController
     */
    public $Controller;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Controller = new ExampleController;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller);
    }

    /**
     * Tests for `_download()` method
     * @test
     */
    public function testDownload()
    {
        $file = tempnam(sys_get_temp_dir(), 'temp');

        $response = $this->invokeMethod($this->Controller, '_download', [$file]);
        $this->assertInstanceOf('Cake\Http\Response', $response);

        $this->assertInstanceOf('Cake\Filesystem\File', $this->getProperty($response, '_file'));
        $this->assertEquals($file, $this->getProperty($response, '_file')->path);

        unlink($file);
    }

    /**
     * Tests for `_download()` method, with a no existing file
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory noExistingFile not readable
     * @test
     */
    public function testDownloadNoExistingFile()
    {
        $this->invokeMethod($this->Controller, '_download', ['noExistingFile']);
    }
}
