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
namespace MeCms\Test\TestCase\View;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use MeCms\View\View;
use MeTools\TestSuite\TestCase;

/**
 * ViewTest class
 */
class ViewTest extends TestCase
{
    /**
     * @var \MeCms\View\View
     */
    protected $View;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $request = new Request;
        $request = $request->env('REQUEST_URI', '/some-page');

        $this->View = new View($request);
        $this->View->plugin = ME_CMS;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload('TestPlugin');
    }

    /**
     * Tests for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertNull($this->View->theme());

        //Loads the `TestPlugin` and sets it as a theme
        $theme = 'TestPlugin';
        Plugin::load($theme);
        Configure::write(ME_CMS . '.default.theme', $theme);

        //Reloads the View
        $this->View = new View(new Request);

        $this->assertEquals($theme, $this->View->theme());
    }

    /**
     * Tests for `getTitleForLayout()` method
     * @test
     */
    public function testGetTitleForLayout()
    {
        $getTitleForLayoutMethod = function () {
            return $this->invokeMethod($this->View, 'getTitleForLayout');
        };

        //Writes the main title on configuration
        $mainTitle = 'main title';
        Configure::write(ME_CMS . '.main.title', $mainTitle);

        $this->assertEquals($getTitleForLayoutMethod(), $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), $mainTitle);

        //Resets the property
        $this->setProperty($this->View, 'titleForLayout', null);

        //Tests the title as if it had been set by the view
        $this->View->Blocks->set('title', 'title from view');
        $this->assertEquals($getTitleForLayoutMethod(), 'title from view - ' . $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), 'title from view - ' . $mainTitle);

        //Resets the property
        $this->setProperty($this->View, 'titleForLayout', null);

        //Tests the title as if it had been set by the controller
        $this->View->set('title', 'title from controller');
        $this->assertEquals($getTitleForLayoutMethod(), 'title from controller - ' . $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), 'title from controller - ' . $mainTitle);

        //It does NOT reset the property. So the title is not modified
        $this->View->Blocks->set('title', 'title from view');
        $this->assertEquals($getTitleForLayoutMethod(), 'title from controller - ' . $mainTitle);

        //If this is the homepage, it only returns the main title from the
        //  configuration, even if you have set another
        $this->View = new View;
        $this->assertEquals($getTitleForLayoutMethod(), $mainTitle);
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
        ], $helpers);
    }

    /**
     * Tests for `renderLayout()` method
     * @test
     */
    public function testRenderLayout()
    {
        //Loads some other helpers
        $this->View->loadHelper(ME_CMS . '.Auth');
        $this->View->loadHelper(ME_CMS . '.Widget');

        //Disable widgets
        Configure::write('Widgets.general', []);

        //Sets a title
        $this->View->set('title', 'title from controller');

        //Creates a favicon
        file_put_contents(WWW_ROOT . 'favicon.ico', null);

        //Renders
        $result = $this->View->render(false, ME_CMS . '.default');

        //@codingStandardsIgnoreLine
        @unlink(WWW_ROOT . 'favicon.ico');

        //Checks for title and favicon
        $this->assertRegExp('/' . preg_quote('<title>title from controller - ' . ME_CMS . '</title>', '/') . '/', $result);
        $this->assertRegExp('/' . preg_quote('<link href="favicon.ico" type="image/x-icon" rel="icon"/><link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>', '/') . '/', $result);
    }
}
