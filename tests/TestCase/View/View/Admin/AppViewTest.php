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

namespace MeCms\Test\TestCase\View\View\Admin;

use Authentication\Identity;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use MeCms\TestSuite\TestCase;
use MeCms\View\Helper\AbstractMenuHelper;
use MeCms\View\View\Admin\AppView;
use MeCms\Example\View\Helper\ExampleMenuHelper;

/**
 * AppViewTest class
 */
class AppViewTest extends TestCase
{
    /**
     * @var \MeCms\View\View\Admin\AppView
     */
    protected AppView $View;

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($this->View)) {
            $Request = new ServerRequest();
            $Request = $Request->withEnv('REQUEST_URI', '/some-page')
                ->withAttribute('identity', new Identity(['group' => ['name' => 'admin']]));
            $this->View = new AppView($Request);
        }
    }

    /**
     * Called after every test method
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removePlugins(['MeCmsExample']);
    }

    /**
     * @test
     * @uses \MeCms\View\View\Admin\AppView::__construct()
     */
    public function testConstruct(): void
    {
        $this->assertEquals('MeCms.admin', $this->View->getLayout());
    }

    /**
     * @test
     * @uses \MeCms\View\View\Admin\AppView::getAllMenuHelpers()
     */
    public function testGetAllMenuHelpers(): void
    {
        $expectedClasses = Configure::readOrFail('MeCms.MenuHelpers');
        $result = $this->View->getAllMenuHelpers();
        $this->assertSame($expectedClasses, array_map('get_class', $result));
        $this->assertContainsOnlyInstancesOf(AbstractMenuHelper::class, $result);
        $this->assertCount(count($expectedClasses), $result);

        //All helpers are loaded
        $loadedHelpers = $this->View->helpers()->loaded();
        foreach ($expectedClasses as $class) {
            $this->assertContains(get_class_short_name($class), $loadedHelpers);
        }

        $this->loadPlugins(['MeCmsExample' => []]);
        $expectedClasses[] = ExampleMenuHelper::class;
        $result = $this->View->getAllMenuHelpers();
        $this->assertSame($expectedClasses, array_map('get_class', $result));
        $this->assertContainsOnlyInstancesOf(AbstractMenuHelper::class, $result);
        $this->assertCount(count($expectedClasses), $result);
        $this->assertContains(ExampleMenuHelper::class, array_map('get_class', $result));

        //Using a bad class
        $this->expectExceptionMessage('Object `stdClass` is not an instance of `MeCms\View\Helper\AbstractMenuHelper`');
        Configure::write('MeCms.MenuHelpers', [...$expectedClasses, \stdClass::class]);
        $this->View->getAllMenuHelpers();
    }

    /**
     * @test
     * @uses \MeCms\View\View\Admin\AppView::render()
     */
    public function testRender(): void
    {
        $this->View->render('StaticPages/page-from-app');
        $this->assertEquals([
            1 => '1 - Very low',
            2 => '2 - Low',
            3 => '3 - Normal',
            4 => '4 - High',
            5 => '5 - Very high',
        ], $this->View->get('priorities'));
    }
}
