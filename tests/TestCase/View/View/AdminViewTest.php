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

use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use MeCms\View\View\AdminView as View;
use Reflection\ReflectionTrait;

/**
 * AdminViewTest class
 */
class AdminViewTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->View = new View(new Request);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->View);
    }

    /**
     * Tests for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertEquals(ME_CMS . '.admin', $this->View->layout());
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        //Gets loaded helpers, as class names
        $helpers = collection($this->View->helpers()->loaded())->map(function ($helper) {
            return get_class($this->View->helpers()->get($helper));
        })->toArray();

        $this->assertEquals([
            METOOLS . '\View\Helper\HtmlHelper',
            METOOLS . '\View\Helper\DropdownHelper',
            METOOLS . '\View\Helper\FormHelper',
            METOOLS . '\View\Helper\LibraryHelper',
            METOOLS . '\View\Helper\PaginatorHelper',
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

        $priorities = $this->View->get('priorities');
        $this->assertEquals([
            1 => '1 - Very low',
            2 => '2 - Low',
            3 => '3 - Normal',
            4 => '4 - High',
            5 => '5 - Very high',
        ], $priorities);
    }
}
