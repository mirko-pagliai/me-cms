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

use Cake\I18n\Time;
use MeCms\Model\Validation\TagValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * TagsTableTest class
 */
class TagsTableTest extends TableTestCase
{
    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Table;

    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
    ];

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules(): void
    {
        $example = ['tag' => 'my tag'];
        $entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity($example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['tag' => ['_isUnique' => I18N_VALUE_ALREADY_USED]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
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
     * Test for `find()` methods
     * @test
     */
    public function testFindMethods(): void
    {
        $query = $this->Table->find('active');
        $this->assertStringEndsWith('FROM tags Tags INNER JOIN posts_tags PostsTags ON Tags.id = (PostsTags.tag_id) INNER JOIN posts Posts ON (Posts.active = :c0 AND Posts.created <= :c1 AND Posts.id = (PostsTags.post_id))', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(Time::class, $query->getValueBinder()->bindings()[':c1']['value']);
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter(): void
    {
        $query = $this->Table->queryFromFilter($this->Table->find(), ['name' => 'test']);
        $this->assertStringEndsWith('FROM tags Tags WHERE tag like :c0', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
    }
}
