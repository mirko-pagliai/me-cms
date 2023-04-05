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

namespace MeCms\Test\TestCase\TestSuite\Admin;

use Laminas\Diactoros\UploadedFile;
use MeCms\Controller\Admin\PostsController;
use MeCms\Model\Table\PostsTable;
use MeCms\Test\TestCase\Controller\Admin\PostsControllerTest;
use MeCms\TestSuite\Admin\ControllerTestCase;
use MeCms\TestSuite\TestCase;

/**
 * ControllerTestCaseTest class
 */
class ControllerTestCaseTest extends TestCase
{
    /**
     * @return void
     * @uses \MeCms\TestSuite\Admin\ControllerTestCase::setUp()
     */
    public function testSetUp(): void
    {
        $this->expectAssertionFailed('You cannot use the `' . ControllerTestCase::class . '` class with a non-admin controller');
        $BadControllerTestCase = new class extends ControllerTestCase {
            public function setUp(): void
            {
                parent::setUp();
            }
        };
        $BadControllerTestCase->setUp();
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\Admin\ControllerTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $expectedUrl = ['controller' => 'Posts', 'prefix' => ADMIN_PREFIX, 'plugin' => 'MeCms'];

        $ControllerTestCase = new PostsControllerTest();
        $this->assertSame('Posts', $ControllerTestCase->alias);
        $this->assertSame(PostsController::class, $ControllerTestCase->originClassName);
        $this->assertInstanceOf(PostsController::class, $ControllerTestCase->Controller);
        $this->assertInstanceOf(PostsTable::class, $ControllerTestCase->Table);
        $this->assertEquals($expectedUrl, $ControllerTestCase->url);

        //The parameters of the request match with the parameters of the url
        foreach ($expectedUrl as $name => $value) {
            $this->assertEquals($value, $ControllerTestCase->Controller->getRequest()->getParam($name));
        }
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\Admin\ControllerTestCase::createImageToUpload()
     */
    public function testCreateImageToUpload(): void
    {
        $ControllerTestCase = new PostsControllerTest();
        $UploadedFile = $ControllerTestCase->createImageToUpload();
        $this->assertInstanceOf(UploadedFile::class, $UploadedFile);
        $this->assertGreaterThan(0, $UploadedFile->getSize());
        $this->assertNotEmpty($UploadedFile->getClientFilename());
    }
}
