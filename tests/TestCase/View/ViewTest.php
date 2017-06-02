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
namespace MeCms\Test\TestCase\View;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use MeCms\View\View;
use Reflection\ReflectionTrait;

/**
 * ViewTest class
 */
class ViewTest extends TestCase
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

        $request = new Request;
        $request = $request->withRequestTarget('/some-page');

        $this->View = new View($request);
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
     * Tests for `_getTitleForLayout()` method
     * @test
     */
    public function testGetTitleForLayout()
    {
        //Writes the main title on configuration
        $mainTitle = 'main title';
        Configure::write(ME_CMS . '.main.title', $mainTitle);

        $result = $this->invokeMethod($this->View, '_getTitleForLayout');
        $this->assertEquals($result, $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), $mainTitle);

        //Resets the property
        $this->setProperty($this->View, 'titleForLayout', null);

        //Tests the title as if it had been set by the view
        $this->View->Blocks->set('title', 'title from view');
        $result = $this->invokeMethod($this->View, '_getTitleForLayout');
        $this->assertEquals($result, 'title from view - ' . $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), 'title from view - ' . $mainTitle);

        //Resets the property
        $this->setProperty($this->View, 'titleForLayout', null);

        //Tests the title as if it had been set by the controller
        $this->View->set('title', 'title from controller');
        $result = $this->invokeMethod($this->View, '_getTitleForLayout');
        $this->assertEquals($result, 'title from controller - ' . $mainTitle);
        $this->assertEquals($this->getProperty($this->View, 'titleForLayout'), 'title from controller - ' . $mainTitle);

        //It does NOT reset the property. So the title is not modified
        $this->View->Blocks->set('title', 'title from view');
        $result = $this->invokeMethod($this->View, '_getTitleForLayout');
        $this->assertEquals($result, 'title from controller - ' . $mainTitle);

        //If this is the homepage, it only returns the main title from the
        //  configuration, even if you have set another
        $this->View = new View;
        $result = $this->invokeMethod($this->View, '_getTitleForLayout');
        $this->assertEquals($result, $mainTitle);
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
        $this->assertNotEmpty($result);

        //@codingStandardsIgnoreLine
        @unlink(WWW_ROOT . 'favicon.ico');

        //Checks for title
        $this->assertRegExp('/' . preg_quote('<title>title from controller - ' . ME_CMS . '</title>', '/') . '/', $result);

        //Checks for favicon
        $this->assertRegExp('/' . preg_quote('<link href="favicon.ico" type="image/x-icon" rel="icon"/><link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>', '/') . '/', $result);
    }
}
