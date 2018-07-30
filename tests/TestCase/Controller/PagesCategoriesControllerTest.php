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
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PagesCategoriesControllerTest class
 */
class PagesCategoriesControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\PagesCategoriesTable
     */
    protected $PagesCategories;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
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

        $this->PagesCategories = TableRegistry::get(ME_CMS . '.PagesCategories');

        Cache::clear(false, $this->PagesCategories->cache);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'pagesCategories']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PagesCategories/index.ctp');

        $categoriesFromView = $this->viewVariable('categories');
        $this->assertNotEmpty($categoriesFromView->toArray());
        $this->assertContainsInstanceof('MeCms\Model\Entity\PagesCategory', $categoriesFromView);

        $cache = Cache::read('categories_index', $this->PagesCategories->cache);
        $this->assertEquals($categoriesFromView->toArray(), $cache->toArray());
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->PagesCategories->find('active')->extract('slug')->first();
        $url = ['_name' => 'pagesCategory', $slug];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PagesCategories/view.ctp');

        $categoryFromView = $this->viewVariable('category');
        $this->assertNotEmpty($categoryFromView);
        $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $categoryFromView);

        $pagesFromView = $this->viewVariable('pages');
        $this->assertNotEmpty($pagesFromView);
        $this->assertContainsInstanceof('MeCms\Model\Entity\Page', $pagesFromView);

        $categoryFromCache = Cache::read(sprintf('category_%s', md5($slug)), $this->PagesCategories->cache);
        $this->assertEquals($categoryFromView, $categoryFromCache->first());

        $pagesFromCache = Cache::read(sprintf('category_%s_pages', md5($slug)), $this->PagesCategories->cache);
        $this->assertEquals($pagesFromView->toArray(), $pagesFromCache->toArray());

        //GET request with query string
        $this->get($url + ['?' => ['q' => $slug]]);
        $this->assertRedirect($url);
    }
}
