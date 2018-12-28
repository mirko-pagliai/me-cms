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

use ErrorException;
use MeCms\Utility\Checkups\KCFinder;
use MeCms\Utility\Checkups\Webroot;
use MeTools\TestSuite\ComponentTestCase;
use Tools\Exception\NotWritableException;

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
     * Test for `getDefaultConfig()` method
     * @test
     */
    public function testGetDefaultConfig()
    {
        $getDefaultConfigMethod = function () {
            return $this->invokeMethod($this->Component, 'getDefaultConfig');
        };

        $expected = [
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
            'uploadDir' => UPLOADED,
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
        ];
        $this->assertEquals($expected, $getDefaultConfigMethod());

        //With an admin user
        $this->Component->Auth->setUser(['group' => ['name' => 'admin']]);
        unset($expected['access']);
        $this->assertEquals($expected, $getDefaultConfigMethod());
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

        //With `uploaded` dir not writable
        $this->assertException(NotWritableException::class, function () {
            $this->Component->Checkup->Webroot = $this->getMockBuilder(Webroot::class)->getMock();
            $this->Component->initialize([]);
        }, 'File or directory `' . rtr(UPLOADED) . '` is not writable');

        //With KCFinder not available
        $this->assertException(ErrorException::class, function () {
            $this->Component->Checkup->KCFinder = $this->getMockBuilder(KCFinder::class)->getMock();
            $this->Component->initialize([]);
        }, 'KCFinder is not available');
    }
}
