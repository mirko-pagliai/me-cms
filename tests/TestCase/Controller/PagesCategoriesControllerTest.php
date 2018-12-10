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
use MeCms\Model\Entity\Page;
use MeCms\Model\Entity\PagesCategory;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PagesCategoriesControllerTest class
 */
class PagesCategoriesControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'pagesCategories']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PagesCategories/index.ctp');
        $this->assertContainsInstanceof(PagesCategory::class, $this->viewVariable('categories'));

        $cache = Cache::read('categories_index', $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('categories')->toArray(), $cache->toArray());
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->Table->find('active')->extract('slug')->first();
        $url = ['_name' => 'pagesCategory', $slug];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PagesCategories/view.ctp');
        $this->assertInstanceof(PagesCategory::class, $this->viewVariable('category'));
        $this->assertContainsInstanceof(Page::class, $this->viewVariable('category')->pages);

        $categoryFromCache = Cache::read(sprintf('category_%s', md5($slug)), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('category'), $categoryFromCache->first());

        //GET request with query string
        $this->get($url + ['?' => ['q' => $slug]]);
        $this->assertRedirect($url);
    }
}
