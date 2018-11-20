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
namespace MeCms\Test\TestCase\View\View;

use MeCms\TestSuite\TestCase;
use MeCms\View\View\AdminView as View;

/**
 * AdminViewTest class
 */
class AdminViewTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $View;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->View = $this->getMockBuilder(View::class)
            ->setMethods(null)
            ->getMock();
    }

    /**
     * Tests for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertEquals(ME_CMS . '.admin', $this->View->getLayout());
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        //Gets loaded helpers, as class names
        $helpers = array_map(function ($helper) {
            return get_class($this->View->helpers()->get($helper));
        }, $this->View->helpers()->loaded());

        $this->assertEquals([
            ME_TOOLS . '\View\Helper\HtmlHelper',
            ME_TOOLS . '\View\Helper\DropdownHelper',
            ME_TOOLS . '\View\Helper\FormHelper',
            ME_TOOLS . '\View\Helper\LibraryHelper',
            ME_TOOLS . '\View\Helper\PaginatorHelper',
            ASSETS . '\View\Helper\AssetHelper',
            'Thumber\View\Helper\ThumbHelper',
            'WyriHaximus\MinifyHtml\View\Helper\MinifyHtmlHelper',
            ME_CMS . '\View\Helper\MenuBuilderHelper',
            'Gourmet\CommonMark\View\Helper\CommonMarkHelper',
        ], $helpers);
    }

    /**
     * Tests for `render()` method
     * @test
     */
    public function testRender()
    {
        $this->View->render(false);
        $this->assertEquals([
            1 => '1 - Very low',
            2 => '2 - Low',
            3 => '3 - Normal',
            4 => '4 - High',
            5 => '5 - Very high',
        ], $this->View->get('priorities'));
    }
}
