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
use Cake\Http\ServerRequest;
use MeCms\TestSuite\TestCase;
use MeCms\View\View\AppView;

/**
 * AppViewTest class
 */
class AppViewTest extends TestCase
{
    /**
     * @var \MeCms\View\View\AppView
     */
    protected AppView $View;

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //Disables widgets and any theme
        Configure::write('Widgets.general', []);
        Configure::write('MeCms.default.theme', false);

        if (empty($this->View)) {
            $Request = new ServerRequest();
            $this->View = new AppView($Request->withEnv('REQUEST_URI', '/some-page'));
        }
    }

    /**
     * @test
     * @uses \MeCms\View\View\AppView::setBlocks()
     */
    public function testSetBlocks(): void
    {
        //Writes some configuration values
        Configure::write('MeCms.shareaholic.site_id', 'shareaholic-id');
        Configure::write('MeCms.default.facebook_app_id', 'facebook-id');

        $result = $this->View->render('StaticPages/page-from-app');
        $this->assertStringContainsString('<link href="/posts/rss" type="application/rss+xml" rel="alternate" title="Latest posts"/>', $result);
        $this->assertStringContainsString('<meta content="MeCms" property="og:title"/>', $result);
        $this->assertStringContainsString('<meta content="http://localhost/" property="og:url"/>', $result);
        $this->assertStringContainsString('<meta content="facebook-id" property="fb:app_id"/>', $result);
        $this->assertStringContainsString('<script src="https://dsms0mj1bbhn4.cloudfront.net/assets/pub/shareaholic.js" async="async" data-cfasync="false" data-shr-siteid="shareaholic-id"></script>', $result);
    }

    /**
     * @test
     * @uses \MeCms\View\View\AppView::renderLayout()
     */
    public function testRenderLayout(): void
    {
        $this->assertNotEmpty($this->View->render('StaticPages/page-from-app'));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals(null, $this->View->getTheme());
    }

    /**
     * @test
     * @uses \MeCms\View\View\AppView::renderLayout()
     */
    public function testRenderLayoutFromTheme(): void
    {
        //Loads the `TestPlugin` and sets it as a theme
        $this->loadPlugins(['TestPlugin' => []]);
        Configure::write('MeCms.default.theme', 'TestPlugin');

        //Reloads the View
        $Request = new ServerRequest();
        $this->View = new AppView($Request->withEnv('REQUEST_URI', '/some-page'));
        $this->assertEquals('This is a layout from TestPlugin', $this->View->render('StaticPages/page-from-app'));
        $this->assertEquals('default', $this->View->getLayout());
        $this->assertEquals('TestPlugin', $this->View->getTheme());
    }
}
