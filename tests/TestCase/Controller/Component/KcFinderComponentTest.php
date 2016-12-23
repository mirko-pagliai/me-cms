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
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use MeCms\Controller\Component\KcFinderComponent;

class KcFinderComponentTest extends TestCase
{
    /**
     * @var \Cake\Controller\ComponentRegistry
     */
    protected $ComponentRegistry;

    /**
     * @var \Cake\Event\Event
     */
    protected $Event;

    /**
     * @var \MeCms\Controller\Component\KcFinderComponent
     */
    protected $KcFinder;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . 'index.php';

        //@codingStandardsIgnoreStart
        @mkdir(dirname($file), 0777, true);
        @mkdir(UPLOADED);
        //@codingStandardsIgnoreEnd

        file_put_contents($file, null);

        $this->ComponentRegistry = new ComponentRegistry(new Controller);
        $this->Event = new Event('test');
        $this->KcFinder = new KcFinderComponent($this->ComponentRegistry);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . 'index.php';

        //@codingStandardsIgnoreStart
        @unlink($file);
        @rmdir(dirname($file));
        //@codingStandardsIgnoreEnd

        unset($this->ComponentRegistry, $this->Event, $this->KcFinder);
    }

    /**
     * Test for `configure()` method
     * @test
     */
    public function testConfigure()
    {
        $this->assertEmpty($this->KcFinder->request->session()->read('KCFINDER'));

        $this->assertNull($this->KcFinder->configure());

        $config = $this->KcFinder->request->session()->read('KCFINDER');

        $this->assertEquals([
            'denyExtensionRename',
            'denyUpdateCheck',
            'dirnameChangeChars',
            'disabled',
            'filenameChangeChars',
            'jpegQuality',
            'uploadDir',
            'uploadURL',
            'types',
            'access',
        ], array_keys($config));

        //Re-call
        $this->assertTrue($this->KcFinder->configure());
    }

    /**
     * Test for `getTypes()` method
     * @test
     */
    public function testGetTypes()
    {
        $this->assertEquals([
            'images' => '*img',
        ], $this->KcFinder->getTypes());

        //@codingStandardsIgnoreLine
        @mkdir(UPLOADED . DS . 'docs');

        $this->assertEquals([
            'docs' => '',
            'images' => '*img',
        ], $this->KcFinder->getTypes());

        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED . DS . 'docs');
    }

    public function testStartup()
    {
        $this->KcFinder = $this->getMockBuilder(KcFinderComponent::class)
            ->setMethods(['configure'])
            ->setConstructorArgs([$this->ComponentRegistry])
            ->getMock();

        $this->KcFinder->method('configure')
            ->will($this->returnCallback(function () {
                return 'called `configure`';
            }));

        $this->assertEquals('called `configure`', $this->KcFinder->startup($this->Event));
    }

    /**
     * Test for `startup()` method, with `uploaded` dir not writable
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory tests/test_app/TestApp/webroot/files not writeable
     */
    public function testStartupDirNotWritable()
    {
        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED);

        $this->KcFinder->startup($this->Event);
    }

    /**
     * Test for `startup()` method, with KCFinder not available
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage KCFinder is not available
     */
    public function testStartupKCFinderNotAvailable()
    {
        //@codingStandardsIgnoreLine
        @unlink(WWW_ROOT . 'vendor' . DS . 'kcfinder' .DS . 'index.php');

        $this->KcFinder->startup($this->Event);
    }
}
