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
namespace MeCms\Test\TestCase\Model\Table\Others;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * AssociationsSameAliasesTest class
 */
class AssociationsSameAliasesTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
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

        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');
        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        Cache::clearAll();
        Cache::clear(false, $this->Pages->cache);
        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Pages, $this->Posts);
    }

    /**
     * Test for associations with the same alias
     * @test
     */
    public function testAssociationsSameAliases()
    {
        foreach (['Pages', 'Posts'] as $table) {
            $categories = $this->$table->Categories;

            $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $categories);
            $this->assertEquals('Categories', $categories->getName());
            $this->assertEquals(ME_CMS . '.' . $table . 'Categories', $categories->className());

            $category = $categories->find()->first();
            $this->assertNotEmpty($category);
            $this->assertInstanceof('MeCms\Model\Entity\\' . $table . 'Category', $category);
        }
    }
}
