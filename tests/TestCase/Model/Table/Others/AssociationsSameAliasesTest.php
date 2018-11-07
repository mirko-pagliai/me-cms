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
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

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
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
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
