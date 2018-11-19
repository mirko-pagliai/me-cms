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
namespace MeCms\Test\TestCase\Model\Table;

use MeCms\Model\Table\PagesCategoriesTable;
use MeCms\Model\Validation\PageValidator;
use MeCms\TestSuite\PostsAndPagesTablesTestCase;

/**
 * PagesTableTest class
 */
class PagesTableTest extends PostsAndPagesTablesTestCase
{
    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
    ];

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('pages', $this->Table->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $this->loadFixtures();

        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());

        $entity = $this->Table->newEntity([
            'category_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ]);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['category_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('pages', $this->Table->getTable());
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Categories);
        $this->assertEquals('category_id', $this->Table->Categories->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Categories->getJoinType());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->Table->Categories->className());
        $this->assertInstanceOf(PagesCategoriesTable::class, $this->Table->Categories->getTarget());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->Table->Categories->getTarget()->getRegistryAlias());
        $this->assertEquals('Categories', $this->Table->Categories->getAlias());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);

        $this->assertInstanceOf(PageValidator::class, $this->Table->getValidator());
    }
}
