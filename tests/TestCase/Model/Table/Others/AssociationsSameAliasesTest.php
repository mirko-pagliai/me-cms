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
namespace MeCms\Test\TestCase\Model\Table\Others;

use Cake\Cache\Cache;
use MeCms\Model\Table\PagesTable;
use MeCms\Model\Table\PostsTable;
use MeCms\TestSuite\TableTestCase;
use MeTools\TestSuite\Traits\MockTrait;

/**
 * AssociationsSameAliasesTest class
 */
class AssociationsSameAliasesTest extends TableTestCase
{
    use MockTrait;

    /**
     * @var object
     */
    protected $Pages;

    /**
     * @var object
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Pages = $this->getMockForTable(PagesTable::class, null);
        $this->Posts = $this->getMockForTable(PostsTable::class, null);

        Cache::clearAll();
        Cache::clear(false, $this->Pages->cache);
        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Test for associations with the same alias
     * @test
     */
    public function testAssociationsSameAliases()
    {
        foreach (['Pages', 'Posts'] as $table) {
            $categories = $this->$table->Categories;

            $this->assertBelongsTo($categories);
            $this->assertEquals('Categories', $categories->getName());
            $this->assertEquals(ME_CMS . '.' . $table . 'Categories', $categories->className());

            $category = $categories->find()->first();
            $this->assertNotEmpty($category);
            $this->assertInstanceof(ME_CMS . '\Model\Entity\\' . $table . 'Category', $category);
        }
    }
}
