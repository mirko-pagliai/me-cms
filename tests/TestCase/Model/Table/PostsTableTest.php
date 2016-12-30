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
namespace MeCms\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * PostsTableTest class
 */
class PostsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
        'plugin.me_cms.users',
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

        $this->Posts = TableRegistry::get('MeCms.Posts');

        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Posts);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('posts', $this->Posts->cache);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $this->Posts = $this->getMockBuilder(get_class($this->Posts))
            ->setMethods(['setNextToBePublished'])
            ->setConstructorArgs([['table' => $this->Posts->table(), 'connection' => $this->Posts->connection()]])
            ->getMock();

        $this->Posts->expects($this->once())
            ->method('setNextToBePublished');

        $this->Posts->afterDelete(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        $this->Posts = $this->getMockBuilder(get_class($this->Posts))
            ->setMethods(['setNextToBePublished'])
            ->setConstructorArgs([['table' => $this->Posts->table(), 'connection' => $this->Posts->connection()]])
            ->getMock();

        $this->Posts->expects($this->once())
            ->method('setNextToBePublished');

        $this->Posts->afterSave(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('posts', $this->Posts->table());
        $this->assertEquals('title', $this->Posts->displayField());
        $this->assertEquals('id', $this->Posts->primaryKey());

        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->Posts->Categories));
        $this->assertEquals('category_id', $this->Posts->Categories->foreignKey());
        $this->assertEquals('INNER', $this->Posts->Categories->joinType());
        $this->assertEquals('MeCms.PostsCategories', $this->Posts->Categories->className());

        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->Posts->Users));
        $this->assertEquals('user_id', $this->Posts->Users->foreignKey());
        $this->assertEquals('INNER', $this->Posts->Users->joinType());
        $this->assertEquals('MeCms.Users', $this->Posts->Users->className());

        $this->assertEquals('Cake\ORM\Association\BelongsToMany', get_class($this->Posts->Tags));
        $this->assertEquals('post_id', $this->Posts->Tags->foreignKey());
        $this->assertEquals('tag_id', $this->Posts->Tags->targetForeignKey());
        $this->assertEquals('MeCms.Tags', $this->Posts->Tags->className());

        //Missing checks for `joinTable` and `through` options
        $this->markTestIncomplete('This test has not been implemented yet');

        $this->assertTrue($this->Posts->hasBehavior('Timestamp'));
        $this->assertTrue($this->Posts->hasBehavior('CounterCache'));
    }

    /**
     * Test for the `belongsToMany` association with `Tags`
     * @test
     */
    public function testBelongsToManyTags()
    {
        $post = $this->Posts->findById(2)->contain(['Tags'])->first();

        $this->assertNotEmpty($post->tags);

        foreach ($post->tags as $tag) {
            $this->assertEquals('MeCms\Model\Entity\Tag', get_class($tag));
            $this->assertEquals('MeCms\Model\Entity\PostsTag', get_class($tag->_joinData));
            $this->assertEquals(2, $tag->_joinData->post_id);
        }
    }

    /**
     * Test for the `belongsTo` association with `PostsCategories`
     * @test
     */
    public function testBelongsToPostsCategories()
    {
        $post = $this->Posts->findById(2)->contain(['Categories'])->first();

        $this->assertNotEmpty($post->category);

        $this->assertEquals('MeCms\Model\Entity\PostsCategory', get_class($post->category));
        $this->assertEquals(4, $post->category->id);
    }

    /**
     * Test for the `belongsTo` association with `Users`
     * @test
     */
    public function testBelongsToUsers()
    {
        $post = $this->Posts->findById(2)->contain(['Users'])->first();

        $this->assertNotEmpty($post->user);

        $this->assertEquals('MeCms\Model\Entity\User', get_class($post->user));
        $this->assertEquals(4, $post->user->id);
    }

    /**
     * Test for `buildTagsForRequestData()` method
     * @test
     */
    public function testBuildTagsForRequestData()
    {
        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $this->Posts = $this->getMockBuilder(get_class($this->Posts))
            ->setMethods(['getNextToBePublished', 'setNextToBePublished'])
            ->setConstructorArgs([['table' => $this->Posts->table(), 'connection' => $this->Posts->connection()]])
            ->getMock();

        $this->Posts->expects($this->at(0))
            ->method('getNextToBePublished')
            ->will($this->returnValue(time() + 3600));

        $this->Posts->expects($this->at(1))
            ->method('getNextToBePublished')
            ->will($this->returnValue(time() - 3600));

        $this->Posts->expects($this->at(2))
            ->method('setNextToBePublished');

        $this->Posts->find();
        $this->Posts->find();

        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for `getRelated()` method
     * @test
     */
    public function testGetRelated()
    {
        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['tag' => 'test'];

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Posts.id AS `Posts__id`, Posts.category_id AS `Posts__category_id`, Posts.user_id AS `Posts__user_id`, Posts.title AS `Posts__title`, Posts.slug AS `Posts__slug`, Posts.subtitle AS `Posts__subtitle`, Posts.text AS `Posts__text`, Posts.priority AS `Posts__priority`, Posts.created AS `Posts__created`, Posts.modified AS `Posts__modified`, Posts.active AS `Posts__active`, PostsTags.id AS `PostsTags__id`, PostsTags.tag_id AS `PostsTags__tag_id`, PostsTags.post_id AS `PostsTags__post_id`, Tags.id AS `Tags__id`, Tags.tag AS `Tags__tag`, Tags.post_count AS `Tags__post_count`, Tags.created AS `Tags__created`, Tags.modified AS `Tags__modified` FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.tag = :c0 AND Tags.id = (PostsTags.tag_id))', $query->sql());

        $this->assertEquals('test', $query->valueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\PostValidator',
            get_class($this->Posts->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
