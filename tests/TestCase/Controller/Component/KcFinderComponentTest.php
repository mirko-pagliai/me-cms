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
use Cake\TestSuite\TestCase;
use MeCms\Controller\Component\KcFinderComponent;
use Reflection\ReflectionTrait;

/**
 * KcFinderComponentTest class
 */
class KcFinderComponentTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Controller\Component\KcFinderComponent
     */
    protected $KcFinder;

    /**
     * Internal method to get a KcFinder instance
     * @return \MeCms\Controller\Component\KcFinderComponent
     */
    protected function getKcFinderInstance()
    {
        return new KcFinderComponent(new ComponentRegistry(new Controller));
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

        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . 'index.php';

        //@codingStandardsIgnoreStart
        @mkdir(dirname($file), 0777, true);
        @mkdir(UPLOADED);
        //@codingStandardsIgnoreEnd

        file_put_contents($file, null);

        $this->KcFinder = $this->getKcFinderInstance();
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

        unset($this->KcFinder);
    }

    /**
     * Test for `_getDefaultConfig()` method
     * @test
     */
    public function testGetDefaultConfig()
    {
        $defaultConfig = $this->invokeMethod($this->KcFinder, '_getDefaultConfig');
        $defaultConfig['uploadDir'] = rtr($defaultConfig['uploadDir']);
        $this->assertEquals([
            'denyExtensionRename' => true,
            'denyUpdateCheck' => true,
            'dirnameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'disabled' => false,
            'filenameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'jpegQuality' => 100,
            'uploadDir' => 'tests/test_app/TestApp/webroot/files/',
            'uploadURL' => 'http://localhost/files',
            'types' => [
                'images' => '*img',
            ],
            'access' => [
                'dirs' => [
                    'create' => true,
                    'delete' => false,
                    'rename' => false,
                ],
                'files' => [
                    'upload' => true,
                    'delete' => false,
                    'copy' => true,
                    'move' => false,
                    'rename' => false,
                ],
            ],
        ], $defaultConfig);

        //Tries with admin user
        $this->KcFinder->Auth->setUser(['group' => ['name' => 'admin']]);

        $defaultConfig = $this->invokeMethod($this->KcFinder, '_getDefaultConfig');
        $defaultConfig['uploadDir'] = rtr($defaultConfig['uploadDir']);
        $this->assertEquals([
            'denyExtensionRename' => true,
            'denyUpdateCheck' => true,
            'dirnameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'disabled' => false,
            'filenameChangeChars' => [
                ' ' => '_',
                ':' => '_',
            ],
            'jpegQuality' => (int)100,
            'uploadDir' => 'tests/test_app/TestApp/webroot/files/',
            'uploadURL' => 'http://localhost/files',
            'types' => [
                'images' => '*img',
            ],
        ], $defaultConfig);
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
        @mkdir(UPLOADED . 'docs');

        $this->assertEquals([
            'docs' => '',
            'images' => '*img',
        ], $this->KcFinder->getTypes());

        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED . 'docs');
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $config = $this->KcFinder->request->session()->read('KCFINDER');
        $this->assertNotEmpty($config);

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
    }

    /**
     * Test for `initialize()` method, with `uploaded` dir not writable
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory tests/test_app/TestApp/webroot/files/ not writeable
     * @test
     */
    public function testInitializeDirNotWritable()
    {
        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED);

        $this->getKcFinderInstance();
    }

    /**
     * Test for `initialize()` method, with KCFinder not available
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage KCFinder is not available
     * @test
     */
    public function testInitializeKCFinderNotAvailable()
    {
        //@codingStandardsIgnoreLine
        @unlink(WWW_ROOT . 'vendor' . DS . 'kcfinder' .DS . 'index.php');

        $this->getKcFinderInstance();
    }
}
