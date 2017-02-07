<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\View\View;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use MeCms\View\View\AppView as View;

/**
 * AppViewTest class
 */
class AppViewTest extends TestCase
{
    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        //Disable widgets
        Configure::write('Widgets.general', []);

        $this->View = new View(new Request);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload('TestPlugin');

        unset($this->View);
    }

    /**
     * Tests for `_setBlocks()` method
     * @test
     */
    public function testSetBlocks()
    {
        //Writes some configuration values
        Configure::write('MeCms.default.toolbar_color', '#ffffff');
        Configure::write('MeCms.default.analytics', 'analytics-id');
        Configure::write('MeCms.shareaholic.site_id', 'shareaholic-id');
        Configure::write('MeCms.default.facebook_app_id', 'facebook-id');

        $result = $this->View->render(false);

        $this->assertRegExp('/' . preg_quote('<meta name="theme-color" content="#ffffff"/>', '/') . '/', $result);
        $this->assertRegExp('/' . preg_quote('<link href="/posts/rss" type="application/rss+xml" rel="alternate" title="Latest posts"/>', '/') . '/', $result);
        $this->assertRegExp('/' . preg_quote('<meta content="MeCms" property="og:title"/>', '/') . '/', $result);
        $this->assertRegExp('/' . preg_quote('<meta content="http://localhost/" property="og:url"/>', '/') . '/', $result);
        $this->assertRegExp('/' . preg_quote('<meta content="facebook-id" property="fb:app_id"/>', '/') . '/', $result);

        $this->assertRegExp('/' . preg_quote('<script>
//<![CDATA[
!function(e,a,t,n,c,o,s){e.GoogleAnalyticsObject=c,e[c]=e[c]||function(){(e[c].q=e[c].q||[]).push(arguments)},e[c].l=1*new Date,o=a.createElement(t),s=a.getElementsByTagName(t)[0],o.async=1,o.src=n,s.parentNode.insertBefore(o,s)}(window,document,"script","//www.google-analytics.com/analytics.js","ga"),ga("create","analytics-id","auto"),ga("send","pageview");
//]]>
</script>', '/') . '/', $result);
        $this->assertRegExp('/' . preg_quote('<script src="//dsms0mj1bbhn4.cloudfront.net/assets/pub/shareaholic.js" async="async" data-cfasync="false" data-shr-siteid="shareaholic-id"></script>', '/') . '/', $result);
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
            'MeTools\View\Helper\HtmlHelper',
            'MeTools\View\Helper\DropdownHelper',
            'MeTools\View\Helper\FormHelper',
            'MeTools\View\Helper\LibraryHelper',
            'MeTools\View\Helper\PaginatorHelper',
            'Assets\View\Helper\AssetHelper',
            'Thumber\View\Helper\ThumbHelper',
            'WyriHaximus\MinifyHtml\View\Helper\MinifyHtmlHelper',
            'MeTools\View\Helper\BBCodeHelper',
            'MeTools\View\Helper\BreadcrumbsHelper',
            'MeTools\View\Helper\RecaptchaHelper',
            'MeCms\View\Helper\WidgetHelper',
        ], $helpers);
    }

    /**
     * Tests for `renderLayout()` method
     * @test
     */
    public function testRenderLayout()
    {
        $result = $this->View->render(false);
        $this->assertNotEmpty($result);
        $this->assertEquals('default', $this->View->layout());
        $this->assertEquals(MECMS, $this->View->plugin);
        $this->assertEquals(null, $this->View->theme());
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
        Configure::write(MECMS . '.default.theme', $theme);

        //Reloads the View
        $this->View = new View(new Request);

        $result = $this->View->render(false);
        $this->assertEquals('This is a layout from TestPlugin', $result);
        $this->assertEquals('default', $this->View->layout());
        $this->assertEquals($theme, $this->View->plugin);
        $this->assertEquals($theme, $this->View->theme());
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
