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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

/**
 * TagsTableTest class
 */
class TagsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Tags;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
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

        $this->Tags = TableRegistry::get(ME_CMS . '.Tags');

        Cache::clear(false, $this->Tags->cache);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('posts', $this->Tags->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = ['tag' => 'my tag'];

        $entity = $this->Tags->newEntity($example);
        $this->assertNotEmpty($this->Tags->save($entity));

        //Saves again the same entity
        $entity = $this->Tags->newEntity($example);
        $this->assertFalse($this->Tags->save($entity));
        $this->assertEquals(['tag' => ['_isUnique' => 'This value is already used']], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('tags', $this->Tags->getTable());
        $this->assertEquals('tag', $this->Tags->getDisplayField());
        $this->assertEquals('id', $this->Tags->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $this->Tags->Posts);
        $this->assertEquals('tag_id', $this->Tags->Posts->getForeignKey());
        $this->assertEquals('post_id', $this->Tags->Posts->getTargetForeignKey());
        $this->assertEquals('posts_tags', $this->Tags->Posts->junction()->getTable());
        $this->assertEquals(ME_CMS . '.Posts', $this->Tags->Posts->className());
        $this->assertEquals(ME_CMS . '.PostsTags', $this->Tags->Posts->getThrough());

        $this->assertTrue($this->Tags->hasBehavior('Timestamp'));

        $this->assertInstanceOf('MeCms\Model\Validation\TagValidator', $this->Tags->validator());
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Tags->find('active');
        $this->assertStringEndsWith('FROM tags Tags INNER JOIN posts_tags PostsTags ON Tags.id = (PostsTags.tag_id) INNER JOIN posts Posts ON (Posts.active = :c0 AND Posts.created <= :c1 AND Posts.id = (PostsTags.post_id))', $query->sql());
        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf('Cake\I18n\Time', $query->valueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['name' => 'test'];

        $query = $this->Tags->queryFromFilter($this->Tags->find(), $data);
        $this->assertStringEndsWith('FROM tags Tags WHERE Tags.tag like :c0', $query->sql());
        $this->assertEquals('%test%', $query->valueBinder()->bindings()[':c0']['value']);
    }
}
