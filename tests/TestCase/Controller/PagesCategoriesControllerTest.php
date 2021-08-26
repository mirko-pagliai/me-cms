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
    public function testIndex(): void
    {
        $this->get(['_name' => 'pagesCategories']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PagesCategories' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(PagesCategory::class, $this->viewVariable('categories'));
        $cache = Cache::read('categories_index', $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('categories')->toArray(), $cache->toArray());
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView(): void
    {
        $url = ['_name' => 'pagesCategory', 'first-page-category'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PagesCategories' . DS . 'view.php');
        $this->assertInstanceof(PagesCategory::class, $this->viewVariable('category'));
        $this->assertContainsOnlyInstancesOf(Page::class, $this->viewVariable('category')->get('pages'));
        $cache = Cache::read('category_' . md5('first-page-category'), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('category'), $cache->first());

        //GET request with query string
        $this->get($url + ['?' => ['q' => 'first-page-category']]);
        $this->assertRedirect($url);
    }
}
