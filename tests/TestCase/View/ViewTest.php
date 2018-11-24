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
use MeCms\TestSuite\TestCase;
use MeCms\View\View;
use MeTools\TestSuite\Traits\MockTrait;

/**
 * ViewTest class
 */
class ViewTest extends TestCase
{
    use MockTrait;

    /**
     * @var \MeCms\View\View
     */
    protected $View;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $request = new Request;
        $request = $request->withEnv('REQUEST_URI', '/some-page');
        $this->View = $this->getMockBuilder(View::class)
            ->setMethods(null)
            ->setConstructorArgs([$request])
            ->getMock();
        $this->View->plugin = 'MeCms';
    }

    /**
     * Called after every test method
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
        $this->assertNull($this->View->getTheme());

        //Loads the `TestPlugin` and sets it as a theme
        $theme = 'TestPlugin';
        Plugin::load($theme);
        Configure::write('MeCms.default.theme', $theme);

        $this->assertEquals($theme, (new View)->getTheme());
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
        Configure::write('MeCms.main.title', $mainTitle);
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
        $helpers = array_map(function ($helper) {
            return get_class($this->View->helpers()->get($helper));
        }, $this->View->helpers()->loaded());

        $this->assertEquals([
            'MeTools\View\Helper\HtmlHelper',
            'MeTools\View\Helper\DropdownHelper',
            'MeTools\View\Helper\FormHelper',
            'MeTools\View\Helper\LibraryHelper',
            'MeTools\View\Helper\PaginatorHelper',
            'Assets\View\Helper\AssetHelper',
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
        $this->View->loadHelper('MeCms.Auth');
        $this->View->loadHelper('MeCms.Widget');

        //Disable widgets
        Configure::write('Widgets.general', []);

        //Sets a title
        $this->View->set('title', 'title from controller');

        //Creates a favicon
        file_put_contents(WWW_ROOT . 'favicon.ico', null);

        //Renders
        $result = $this->View->render(false, 'MeCms.default');

        safe_unlink(WWW_ROOT . 'favicon.ico');

        //Checks for title and favicon
        $this->assertContains('<title>title from controller - ' . 'MeCms</title>', $result);
        $this->assertContains('<link href="favicon.ico" type="image/x-icon" rel="icon"/><link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>', $result);
    }
}
