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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\PagesCategoriesController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * PagesCategoriesControllerTest class
 */
class PagesCategoriesControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\PagesCategoriesController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\PagesCategoriesTable
     */
    protected $PagesCategories;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages_categories',
    ];

    /**
     * @var array
     */
    protected $url;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUserGroup('admin');

        $this->Controller = new PagesCategoriesController;

        $this->PagesCategories = TableRegistry::get(ME_CMS . '.PagesCategories');

        Cache::clear(false, $this->PagesCategories->cache);

        $this->url = ['controller' => 'PagesCategories', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->PagesCategories);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
            $this->assertResponseOk();
            $this->assertNotEmpty($this->viewVariable('categories'));
        }
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

        //`delete` action
        $this->Controller = new PagesCategoriesController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'delete');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PagesCategories/index.ctp');

        $categoriesFromView = $this->viewVariable('categories');
        $this->assertInstanceof('Cake\ORM\Query', $categoriesFromView);
        $this->assertNotEmpty($categoriesFromView);

        foreach ($categoriesFromView as $category) {
            $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $category);
        }
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PagesCategories/add.ctp');

        $categoryFromView = $this->viewVariable('category');
        $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $categoryFromView);
        $this->assertNotEmpty($categoryFromView);

        //POST request. Data are valid
        $this->post($url, [
            'title' => 'new category',
            'slug' => 'new-category-slug',
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $categoryFromView = $this->viewVariable('category');
        $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $categoryFromView);
        $this->assertNotEmpty($categoryFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PagesCategories/edit.ctp');

        $categoryFromView = $this->viewVariable('category');
        $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $categoryFromView);
        $this->assertNotEmpty($categoryFromView);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $categoryFromView = $this->viewVariable('category');
        $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $categoryFromView);
        $this->assertNotEmpty($categoryFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $id = $this->PagesCategories->find()->where(['page_count <' => 1])->extract('id')->first();

        //POST request. This category has no pages
        $this->post(array_merge($this->url, ['action' => 'delete', $id]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $id = $this->PagesCategories->find()->where(['page_count >=' => 1])->extract('id')->first();

        //POST request. This category has some pages, so it cannot be deleted
        $this->post(array_merge($this->url, ['action' => 'delete', $id]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession(
            'Before deleting this, you must delete or reassign all items that belong to this element',
            'Flash.flash.0.message'
        );
    }
}
