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

namespace MeCms\Test\TestCase\View;

use Cake\Core\Configure;
use MeCms\TestSuite\TestCase;
use MeCms\View\View;
use Tools\Filesystem;

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
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->View = new View();
        $this->View->setPlugin('MeCms');
        $this->View->setRequest($this->View->getRequest()->withEnv('REQUEST_URI', '/some-page'));
    }

    /**
     * Tests for `__construct()` method
     * @test
     */
    public function testConstruct(): void
    {
        $this->assertNull($this->View->getTheme());

        //Loads the `TestPlugin` and sets it as a theme
        $this->loadPlugins(['TestPlugin']);
        Configure::write('MeCms.default.theme', 'TestPlugin');
        $this->assertEquals('TestPlugin', (new View())->getTheme());
    }

    /**
     * Tests for `getTitleForLayout()` method
     * @test
     */
    public function testGetTitleForLayout(): void
    {
        $getTitleForLayoutMethod = function () {
            return $this->invokeMethod($this->View, 'getTitleForLayout');
        };

        //Writes the main title on configuration
        $mainTitle = 'main title';
        Configure::write('MeCms.main.title', $mainTitle);
        $this->assertEquals($getTitleForLayoutMethod(), $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), $mainTitle);

        //Tests the title as if it had been set by the view
        $this->setProperty($this->View, 'titleForLayout', null);
        $this->View->assign('title', 'title from view');
        $this->assertEquals($getTitleForLayoutMethod(), 'title from view - ' . $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), 'title from view - ' . $mainTitle);

        //Tests the title as if it had been set by the controller
        $this->setProperty($this->View, 'titleForLayout', null);
        $this->View->set('title', 'title from controller');
        $this->assertEquals($getTitleForLayoutMethod(), 'title from controller - ' . $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), 'title from controller - ' . $mainTitle);

        //It does NOT reset the property. So the title is not modified
        $this->View->assign('title', 'title from view');
        $this->assertEquals($getTitleForLayoutMethod(), 'title from controller - ' . $mainTitle);

        //If this is the homepage, it only returns the main title from the
        //  configuration, even if you have set another
        $this->View = new View();
        $request = $this->View->getRequest()->withEnv('REQUEST_URI', '/')->withParam('controller', 'Posts')->withParam('action', 'index');
        $this->View->setRequest($request);
        $this->assertEquals($getTitleForLayoutMethod(), $mainTitle);
    }

    /**
     * Tests for `renderLayout()` method
     * @test
     */
    public function testRenderLayout(): void
    {
        //Disable widgets, sets title, creates a favicon, then renders
        Configure::write('Widgets.general', []);
        $this->View->set('title', 'title from controller');
        (new Filesystem())->createFile(WWW_ROOT . 'favicon.ico');
        $this->View->loadHelper('MeCms.Auth');
        $this->View->loadHelper('MeCms.Widget');
        $result = $this->View->render('StaticPages/page-from-app', 'MeCms.default');

        //Checks for title and favicon
        $this->assertStringContainsString('<title>title from controller - MeCms</title>', $result);
        $this->assertStringContainsString('<link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>', $result);
        @unlink(WWW_ROOT . 'favicon.ico');
    }
}
