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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

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

        $this->PagesCategories = TableRegistry::get('MeCms.PagesCategories');

        Cache::clear(false, $this->PagesCategories->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PagesCategories);
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
        $this->get(['_name' => 'pagesCategories']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PagesCategories/index.ctp');

        $viewVariable = $this->viewVariable('categories');
        $this->assertInstanceof('Cake\ORM\ResultSet', $viewVariable);

        foreach ($viewVariable as $category) {
            $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $category);
        }

        $cache = Cache::read('categories_index', $this->PagesCategories->cache);
        $this->assertEquals($viewVariable->toArray(), $cache->toArray());
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->PagesCategories->find('active')
            ->order([sprintf('%s.id', $this->PagesCategories->getAlias()) => 'ASC'])
            ->extract('slug')
            ->first();

        $url = ['_name' => 'pagesCategory', $slug];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PagesCategories/view.ctp');

        $viewVariable = $this->viewVariable('category');
        $this->assertInstanceof('MeCms\Model\Entity\PagesCategory', $viewVariable);

        $cache = Cache::read(sprintf('category_%s', md5($slug)), $this->PagesCategories->cache);
        $this->assertEquals($viewVariable, $cache->first());

        $this->get(array_merge($url, ['?' => ['q' => $slug]]));
        $this->assertRedirect($url);
    }
}
