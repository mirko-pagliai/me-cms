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

use MeCms\Utility\Checkups\KCFinder;
use MeCms\Utility\Checkups\Webroot;
use MeTools\TestSuite\ComponentTestCase;

/**
 * KcFinderComponentTest class
 */
class KcFinderComponentTest extends ComponentTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        create_kcfinder_files();

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        safe_unlink_recursive(KCFINDER, 'empty');

        parent::tearDown();
    }

    /**
     * Test for `getDefaultConfig()` method
     * @test
     */
    public function testGetDefaultConfig()
    {
        $getDefaultConfigMethod = function () {
            $defaultConfig = $this->invokeMethod($this->Component, 'getDefaultConfig');
            $defaultConfig['uploadDir'] = rtr($defaultConfig['uploadDir']);

            return $defaultConfig;
        };

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
        ], $getDefaultConfigMethod());

        //Tries with admin user
        $this->Component->Auth->setUser(['group' => ['name' => 'admin']]);

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
        ], $getDefaultConfigMethod());
    }

    /**
     * Test for `getTypes()` method
     * @test
     */
    public function testGetTypes()
    {
        $this->assertEquals(['images' => '*img'], $this->Component->getTypes());

        safe_mkdir(UPLOADED . 'docs');
        $this->assertEquals(['docs' => '', 'images' => '*img'], $this->Component->getTypes());

        safe_rmdir(UPLOADED . 'docs');
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertArrayKeysEqual([
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
        ], $this->Component->request->getSession()->read('KCFINDER'));
    }

    /**
     * Test for `initialize()` method, with `uploaded` dir not writable
     * @expectedException RuntimeException
     * @expectedExceptionMessage File or directory tests/test_app/TestApp/webroot/files/ not writeable
     * @test
     */
    public function testInitializeDirNotWritable()
    {
        $this->Component->Checkup->Webroot = $this->getMockBuilder(Webroot::class)->getMock();
        $this->Component->initialize([]);
    }

    /**
     * Test for `initialize()` method, with KCFinder not available
     * @expectedException RuntimeException
     * @expectedExceptionMessage KCFinder is not available
     * @test
     */
    public function testInitializeKCFinderNotAvailable()
    {
        $this->Component->Checkup->KCFinder = $this->getMockBuilder(KCFinder::class)->getMock();
        $this->Component->initialize([]);
    }
}
