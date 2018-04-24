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
use MeCms\Controller\Component\KcFinderComponent;
use MeTools\TestSuite\TestCase;

/**
 * KcFinderComponentTest class
 */
class KcFinderComponentTest extends TestCase
{
    /**
     * @var \Cake\Controller\ComponentRegistry
     */
    protected $ComponentRegistry;

    /**
     * @var \MeCms\Controller\Component\KcFinderComponent
     */
    protected $KCFinder;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->ComponentRegistry = new ComponentRegistry(new Controller);
        $this->KCFinder = new KcFinderComponent($this->ComponentRegistry);
    }

    /**
     * Test for `getDefaultConfig()` method
     * @test
     */
    public function testGetDefaultConfig()
    {
        $defaultConfig = $this->invokeMethod($this->KCFinder, 'getDefaultConfig');
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
        $this->KCFinder->Auth->setUser(['group' => ['name' => 'admin']]);

        $defaultConfig = $this->invokeMethod($this->KCFinder, 'getDefaultConfig');
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
        $this->assertEquals(['images' => '*img'], $this->KCFinder->getTypes());

        safe_mkdir(UPLOADED . 'docs');

        $this->assertEquals(['docs' => '', 'images' => '*img'], $this->KCFinder->getTypes());

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
        ], $this->KCFinder->request->session()->read('KCFINDER'));
    }

    /**
     * Test for `initialize()` method, with `uploaded` dir not writable
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory tests/test_app/TestApp/webroot/files/ not writeable
     * @test
     */
    public function testInitializeDirNotWritable()
    {
        $this->KCFinder->Checkup->Webroot = $this->getMockBuilder(get_class($this->KCFinder->Checkup->Webroot))
            ->getMock();

        $this->KCFinder->initialize([]);
    }

    /**
     * Test for `initialize()` method, with KCFinder not available
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage KCFinder is not available
     * @test
     */
    public function testInitializeKCFinderNotAvailable()
    {
        $this->KCFinder->Checkup->KCFinder = $this->getMockBuilder(get_class($this->KCFinder->Checkup->KCFinder))
            ->getMock();

        $this->KCFinder->initialize([]);
    }
}
