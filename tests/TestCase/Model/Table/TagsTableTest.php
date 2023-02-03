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

namespace MeCms\Test\TestCase\Model\Table;

use Cake\I18n\FrozenTime;
use MeCms\Model\Validation\TagValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * TagsTableTest class
 * @property \MeCms\Model\Table\TagsTable $Table
 */
class TagsTableTest extends TableTestCase
{
    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
    ];

    /**
     * Test for `buildRules()` method
     * @test
     * @uses \MeCms\Model\Table\TagsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $example = ['tag' => 'my tag'];
        $Entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($this->Table->save($Entity));

        //Saves again the same entity
        $Entity = $this->Table->newEntity($example);
        $this->assertFalse($this->Table->save($Entity));
        $this->assertEquals(['tag' => ['_isUnique' => I18N_VALUE_ALREADY_USED]], $Entity->getErrors());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\TagsTable::initialize()
     */
    public function testInitialize(): void
    {
        $this->assertEquals('tags', $this->Table->getTable());
        $this->assertEquals('tag', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsToMany($this->Table->Posts);
        $this->assertEquals('tag_id', $this->Table->Posts->getForeignKey());
        $this->assertEquals('post_id', $this->Table->Posts->getTargetForeignKey());
        $this->assertEquals('posts_tags', $this->Table->Posts->junction()->getTable());

        $this->assertHasBehavior('Timestamp');

        $this->assertInstanceOf(TagValidator::class, $this->Table->getValidator());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\TagsTable::findActive()
     */
    public function testFindActive(): void
    {
        $query = $this->Table->find('active');
        $sql = $query->sql();
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(FrozenTime::class, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertSqlEndsWith('FROM tags Tags INNER JOIN posts_tags PostsTags ON Tags.id = PostsTags.tag_id INNER JOIN posts Posts ON (Posts.active = :c0 AND Posts.created <= :c1 AND Posts.id = PostsTags.post_id)', $sql);
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\TagsTable::queryFromFilter()
     */
    public function testQueryFromFilter(): void
    {
        $query = $this->Table->queryFromFilter($this->Table->find(), ['name' => 'test']);
        $this->assertSqlEndsWith('FROM tags Tags WHERE tag like :c0', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
    }
}
