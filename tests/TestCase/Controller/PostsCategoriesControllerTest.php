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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * PostsCategoriesControllerTest class
 */
class PostsCategoriesControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\PostsCategoriesTable
     */
    protected $PostsCategories;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
        'plugin.me_cms.users',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->PostsCategories = TableRegistry::get(ME_CMS . '.PostsCategories');

        Cache::clear(false, $this->PostsCategories->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PostsCategories);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'postsCategories']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PostsCategories/index.ctp');

        $categoriesFromView = $this->viewVariable('categories');
        $this->assertInstanceof('Cake\ORM\Query', $categoriesFromView);
        $this->assertNotEmpty($categoriesFromView->toArray());

        foreach ($categoriesFromView as $category) {
            $this->assertInstanceof('MeCms\Model\Entity\PostsCategory', $category);
        }

        $cache = Cache::read('categories_index', $this->PostsCategories->cache);
        $this->assertEquals($categoriesFromView->toArray(), $cache->toArray());
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->PostsCategories->find('active')
            ->order([sprintf('%s.id', $this->PostsCategories->getAlias()) => 'ASC'])
            ->extract('slug')
            ->first();

        $url = ['_name' => 'postsCategory', $slug];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PostsCategories/view.ctp');

        $categoryFromView = $this->viewVariable('category');
        $this->assertInstanceof('MeCms\Model\Entity\PostsCategory', $categoryFromView);

        $postsFromView = $this->viewVariable('posts');
        $this->assertInstanceof('Cake\ORM\ResultSet', $postsFromView);
        $this->assertNotEmpty($postsFromView);

        foreach ($postsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }

        //Sets the cache name
        $cache = sprintf('category_%s_limit_%s_page_%s', md5($slug), getConfigOrFail('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsCategories->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOk();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);

        //GET request with query string
        $this->get(array_merge($url, ['?' => ['q' => $slug]]));
        $this->assertRedirect($url);

        //GET request with a no existing category
        $this->get(['_name' => 'postsCategory', 'no-existing']);
        $this->assertResponseError();
    }
}
