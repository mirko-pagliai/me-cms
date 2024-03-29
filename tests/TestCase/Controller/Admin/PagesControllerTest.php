<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use MeCms\Model\Entity\Page;
use MeCms\TestSuite\Admin\ControllerTestCase;

/**
 * PagesControllerTest class
 * @property \MeCms\Model\Table\PagesTable $Table
 * @group admin-controller
 */
class PagesControllerTest extends ControllerTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        Cache::clear('static_pages');

        parent::tearDown();
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::beforeFilter()
     * @test
     */
    public function testBeforeFilter(): void
    {
        foreach (['add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertNotEmpty($this->viewVariable('categories'));
        }

        $this->get($this->url + ['action' => 'index']);
        $this->assertNotEmpty($this->viewVariable('categories'));

        //The `indexStatics` action still works
        $this->get($this->url + ['action' => 'indexStatics']);
        $this->assertEmpty($this->viewVariable('categories'));

        //Deletes all categories
        $this->Table->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'PagesCategories', 'action' => 'index']);
            $this->assertFlashMessage('You must first create a category');
        }

        //The `indexStatics` action still works
        $this->get($this->url + ['action' => 'indexStatics']);
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::isAuthorized()
     * @test
     */
    public function testIsAuthorized(): void
    {
        parent::testIsAuthorized();

        //With `index` and `indexStatics` actions
        foreach (['index', 'indexStatics'] as $action) {
            $this->assertAllGroupsAreAuthorized($action);
        }
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::index()
     * @test
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Pages' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(Page::class, $this->viewVariable('pages'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::indexStatics()
     * @test
     */
    public function testIndexStatics(): void
    {
        $this->get($this->url + ['action' => 'indexStatics']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Pages' . DS . 'index_statics.php');
        $this->assertContainsOnlyInstancesOf(Entity::class, $this->viewVariable('pages'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::add()
     * @test
     */
    public function testAdd(): void
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Pages' . DS . 'form.php');
        $this->assertInstanceOf(Page::class, $this->viewVariable('page'));

        //POST request. Data are valid
        $this->post($url, [
            'category_id' => 1,
            'title' => 'new page title',
            'slug' => 'new-page-slug',
            'text' => 'new page text',
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(Page::class, $this->viewVariable('page'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::edit()
     * @test
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Pages' . DS . 'form.php');
        $this->assertInstanceOf(Page::class, $this->viewVariable('page'));

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(Page::class, $this->viewVariable('page'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PagesController::delete()
     * @test
     */
    public function testDelete(): void
    {
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(1)->all()->isEmpty());
    }
}
