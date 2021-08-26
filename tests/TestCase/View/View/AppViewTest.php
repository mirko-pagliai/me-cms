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

namespace MeCms\Test\TestCase\View\View;

use Cake\Core\Configure;
use MeCms\TestSuite\TestCase;
use MeCms\View\View\AppView as View;

/**
 * AppViewTest class
 */
class AppViewTest extends TestCase
{
    /**
     * @var \MeCms\View\View\AppView
     */
    protected $View;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        //Disables widgets and any theme
        Configure::write('Widgets.general', []);
        Configure::write('MeCms.default.theme', false);

        $this->View = new View();
        $this->View->setRequest($this->View->getRequest()->withEnv('REQUEST_URI', '/some-page'));
        $this->View->setPlugin('MeCms');
    }

    /**
     * Tests for `setBlocks()` method
     * @test
     */
    public function testSetBlocks(): void
    {
        //Writes some configuration values
        Configure::write('MeCms.default.toolbar_color', '#ffffff');
        Configure::write('MeCms.default.analytics', 'analytics-id');
        Configure::write('MeCms.shareaholic.site_id', 'shareaholic-id');
        Configure::write('MeCms.default.facebook_app_id', 'facebook-id');

        $result = $this->View->render('StaticPages/page-from-app');
        $this->assertStringContainsString('<meta name="theme-color" content="#ffffff"/>', $result);
        $this->assertStringContainsString('<link href="/posts/rss" type="application/rss+xml" rel="alternate" title="Latest posts"/>', $result);
        $this->assertStringContainsString('<meta content="MeCms" property="og:title"/>', $result);
        $this->assertStringContainsString('<meta content="http://localhost/" property="og:url"/>', $result);
        $this->assertStringContainsString('<meta content="facebook-id" property="fb:app_id"/>', $result);
        $this->assertStringContainsString('<script>!function(e,a,t,n,c,o,s){e.GoogleAnalyticsObject=c,e[c]=e[c]||function(){(e[c].q=e[c].q||[]).push(arguments)},e[c].l=1*new Date,o=a.createElement(t),s=a.getElementsByTagName(t)[0],o.async=1,o.src=n,s.parentNode.insertBefore(o,s)}(window,document,"script","//www.google-analytics.com/analytics.js","ga"),ga("create","analytics-id","auto"),ga("send","pageview");</script>', $result);
        $this->assertStringContainsString('<script src="//dsms0mj1bbhn4.cloudfront.net/assets/pub/shareaholic.js" async="async" data-cfasync="false" data-shr-siteid="shareaholic-id"></script>', $result);
    }

    /**
     * Tests for `renderLayout()` method
     * @test
     */
    public function testRenderLayout(): void
    {
        $this->assertNotEmpty($this->View->render('StaticPages/page-from-app'));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals(null, $this->View->getTheme());
    }

    /**
     * Tests for `renderLayout()` method, with a layout from a theme
     * @test
     */
    public function testRenderLayoutFromTheme(): void
    {
        //Loads the `TestPlugin` and sets it as a theme
        $this->loadPlugins(['TestPlugin']);
        Configure::write('MeCms.default.theme', 'TestPlugin');

        //Reloads the View
        $this->View = new View();
        $this->View->setRequest($this->View->getRequest()->withEnv('REQUEST_URI', '/some-page'));
        $this->assertEquals('This is a layout from TestPlugin', $this->View->render('StaticPages/page-from-app'));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals('TestPlugin', $this->View->getTheme());
    }

    /**
     * Tests for `addToUserbar()` method
     * @test
     */
    public function testAddToUserbar(): void
    {
        $this->View->addToUserbar('string');
        $this->View->addToUserbar(['first', 'second']);
        $this->View->addToUserbar([['nestled']]);
        $this->View->render('StaticPages/page-from-app');
        $this->assertEquals('<li>string</li>' . PHP_EOL . '<li>first</li>' . PHP_EOL . '<li>second</li>' . PHP_EOL . '<li>nestled</li>', $this->View->fetch('userbar'));
    }
}
