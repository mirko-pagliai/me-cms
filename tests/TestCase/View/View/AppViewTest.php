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

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use MeCms\TestSuite\TestCase;
use MeCms\View\View\AppView as View;

/**
 * AppViewTest class
 */
class AppViewTest extends TestCase
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

        //Disables widgets and any theme
        Configure::write('Widgets.general', []);
        Configure::write(ME_CMS . '.default.theme', false);

        $this->View = $this->getMockBuilder(View::class)
            ->setMethods(null)
            ->getMock();
        $this->View->plugin = ME_CMS;
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
     * Tests for `setBlocks()` method
     * @test
     */
    public function testSetBlocks()
    {
        //Writes some configuration values
        Configure::write(ME_CMS . '.default.toolbar_color', '#ffffff');
        Configure::write(ME_CMS . '.default.analytics', 'analytics-id');
        Configure::write(ME_CMS . '.shareaholic.site_id', 'shareaholic-id');
        Configure::write(ME_CMS . '.default.facebook_app_id', 'facebook-id');

        $result = $this->View->render(false);
        $this->assertContains('<meta name="theme-color" content="#ffffff"/>', $result);
        $this->assertContains('<link href="/posts/rss" type="application/rss+xml" rel="alternate" title="Latest posts"/>', $result);
        $this->assertContains('<meta content="' . ME_CMS . '" property="og:title"/>', $result);
        $this->assertContains('<meta content="http://localhost/" property="og:url"/>', $result);
        $this->assertContains('<meta content="facebook-id" property="fb:app_id"/>', $result);
        $this->assertContains('<script>!function(e,a,t,n,c,o,s){e.GoogleAnalyticsObject=c,e[c]=e[c]||function(){(e[c].q=e[c].q||[]).push(arguments)},e[c].l=1*new Date,o=a.createElement(t),s=a.getElementsByTagName(t)[0],o.async=1,o.src=n,s.parentNode.insertBefore(o,s)}(window,document,"script","//www.google-analytics.com/analytics.js","ga"),ga("create","analytics-id","auto"),ga("send","pageview");</script>', $result);
        $this->assertContains('<script src="//dsms0mj1bbhn4.cloudfront.net/assets/pub/shareaholic.js" async="async" data-cfasync="false" data-shr-siteid="shareaholic-id"></script>', $result);
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
            THUMBER . '\View\Helper\ThumbHelper',
            'WyriHaximus\MinifyHtml\View\Helper\MinifyHtmlHelper',
            ME_TOOLS . '\View\Helper\BBCodeHelper',
            ME_TOOLS . '\View\Helper\BreadcrumbsHelper',
            RECAPTCHA_MAILHIDE . '\View\Helper\MailhideHelper',
            ME_CMS . '\View\Helper\WidgetHelper',
        ], $helpers);
    }

    /**
     * Tests for `renderLayout()` method
     * @test
     */
    public function testRenderLayout()
    {
        $this->assertNotEmpty($this->View->render(false));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals(null, $this->View->getTheme());
    }

    /**
     * Tests for `renderLayout()` method, with a layout from a theme
     * @test
     */
    public function testRenderLayoutFromTheme()
    {
        //Loads the `TestPlugin` and sets it as a theme
        $theme = 'TestPlugin';
        Plugin::load($theme);
        Configure::write(ME_CMS . '.default.theme', $theme);

        //Reloads the View
        $this->View = $this->getMockBuilder(View::class)
            ->setMethods(null)
            ->getMock();
        $this->assertEquals('This is a layout from TestPlugin', $this->View->render(false));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals($theme, $this->View->getTheme());
    }

    /**
     * Tests for `renderLayout()` method, with a layout from the app
     * @test
     */
    public function testRenderLayoutFromApp()
    {
        //Creates a new layout
        $layoutFromApp = first_value(App::path('Template/Plugin/' . ME_CMS . '/Layout')) . 'default.ctp';
        file_put_contents($layoutFromApp, 'This is a layout from app');
        $this->assertEquals('This is a layout from app', $this->View->render(false));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals(ME_CMS, $this->View->plugin);
        $this->assertEquals(null, $this->View->getTheme());

        safe_unlink($layoutFromApp);
    }

    /**
     * Tests for `userbar()` method
     * @test
     */
    public function testUserbar()
    {
        $this->assertEmpty($this->View->userbar());

        $this->View->userbar('string');
        $this->View->userbar(['first', 'second']);
        $this->View->userbar([['nestled']]);
        $this->assertEquals([
            'string',
            'first',
            'second',
            ['nestled'],
        ], $this->View->userbar());

        $this->View->render(false);
        $this->assertEquals('<li>string</li>
<li>first</li>
<li>second</li>
<li>nestled</li>', $this->View->fetch('userbar'));
    }
}
