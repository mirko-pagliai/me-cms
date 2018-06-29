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
use MeCms\Controller\Admin\PagesController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\Admin\PagesController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
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

        $this->Controller = new PagesController;

        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');

        Cache::clear(false, $this->Pages->cache);

        $this->url = ['controller' => 'Pages', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertNotEmpty($this->viewVariable('categories'));
        }

        $this->get($this->url + ['action' => 'index']);
        $this->assertNotEmpty($this->viewVariable('categories'));

        //`indexStatics` still works
        $this->get($this->url + ['action' => 'indexStatics']);
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * Tests for `beforeFilter()` method, with no categories
     * @test
     */
    public function testBeforeFilterNoCategories()
    {
        //Deletes all categories
        $this->Pages->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'PagesCategories', 'action' => 'index']);
            $this->assertFlashMessage('You must first create a category');
        }

        //`indexStatics` still works
        $this->get($this->url + ['action' => 'indexStatics']);
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        foreach (['add', 'edit'] as $action) {
            $this->Controller = new PagesController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
            $this->Controller->initialize();

            $this->assertContains('KcFinder', $this->Controller->components()->loaded());
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

        //`index` and `indexStatics` actions
        foreach (['index', 'indexStatics'] as $action) {
            $this->Controller = new PagesController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);

            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => true,
                'user' => true,
            ]);
        }

        //`delete` action
        $this->Controller = new PagesController;
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
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/index.ctp');

        $pagesFromView = $this->viewVariable('pages');
        $this->assertNotEmpty($pagesFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pagesFromView);
    }

    /**
     * Tests for `indexStatics()` method
     * @test
     */
    public function testIndexStatics()
    {
        $this->get($this->url + ['action' => 'indexStatics']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/index_statics.ctp');

        $pagesFromView = $this->viewVariable('pages');
        $this->assertNotEmpty($pagesFromView);
        $this->assertInstanceof('Cake\ORM\Entity', $pagesFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/add.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertNotEmpty($pageFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);

        //POST request. Data are valid
        $this->post($url, [
            'category_id' => 1,
            'title' => 'new page title',
            'slug' => 'new-page-slug',
            'text' => 'new page text',
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $pageFromView = $this->viewVariable('page');
        $this->assertNotEmpty($pageFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Pages/edit.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertNotEmpty($pageFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);

        //Checks if the `created` field has been properly formatted
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}$/', $pageFromView->created);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $pageFromView = $this->viewVariable('page');
        $this->assertNotEmpty($pageFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
    }
}
