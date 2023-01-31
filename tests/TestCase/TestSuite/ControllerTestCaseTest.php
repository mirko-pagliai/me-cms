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

namespace MeCms\Test\TestCase\TestSuite;

use MeCms\Controller\PostsController;
use MeCms\Model\Table\PostsTable;
use MeCms\Test\TestCase\Controller\PostsControllerTest;
use MeCms\TestSuite\TestCase;

/**
 * ControllerTestCaseTest class
 */
class ControllerTestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\ControllerTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $expectedUrl = ['controller' => 'Posts', 'prefix' => null, 'plugin' => 'MeCms'];

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
}
