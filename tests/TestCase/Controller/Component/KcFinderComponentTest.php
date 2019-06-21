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
use MeCms\Controller\Component\KcFinderComponent;
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
     * Test for `getTypes()` method
     * @test
     */
    public function testGetTypes()
    {
        $this->assertEquals(['images' => '*img'], $this->Component->getTypes());

        @mkdir(UPLOADED . 'docs');
        $this->assertEquals(['docs' => '', 'images' => '*img'], $this->Component->getTypes());
        @rmdir(UPLOADED . 'docs');
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $expected = [
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
            'types' => [
                'images' => '*img',
            ],
            'uploadDir' => UPLOADED,
            'uploadURL' => 'http://localhost/files',
        ];
        $this->assertSame($expected, $this->Component->getConfig());
        $this->assertSame($expected, $this->Component->getController()->request->getSession()->read('KCFINDER'));

        $expected = ['access' => []] + $expected;
        $this->Component->Auth->setUser(['group' => ['name' => 'admin']]);
        $this->Component->initialize([]);
        $this->assertEquals($expected, $this->Component->getConfig());
        $this->assertSame($expected, $this->Component->getController()->request->getSession()->read('KCFINDER'));

        //With `uploaded` dir not writable
        $this->assertException(NotWritableException::class, function () {
            $component = $this->getMockForComponent(KcFinderComponent::class, ['uploadedDirIsWriteable']);
            $component->method('uploadedDirIsWriteable')->willReturn(false);
            $component->initialize([]);
        }, 'File or directory `' . rtr(UPLOADED) . '` is not writable');

        //With KCFinder not available
        $this->assertException(ErrorException::class, function () {
            $component = $this->getMockForComponent(KcFinderComponent::class, ['kcFinderIsAvailable']);
            $component->method('kcFinderIsAvailable')->willReturn(false);
            $component->initialize([]);
        }, 'KCFinder is not available');
    }
}
