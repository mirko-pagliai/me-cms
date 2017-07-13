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

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\ORM\Entity;
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
     * @var array
     */
    protected $example;

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

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        $this->example = [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
            'tags_as_string' => 'first tag, second tag',
        ];

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
     * Test for `_initializeSchema()` method
     * @test
     */
    public function testInitializeSchema()
    {
        $this->assertEquals('json', $this->Posts->getSchema()->columnType('preview'));
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $this->Posts = $this->getMockForModel(get_class($this->Posts), ['setNextToBePublished']);

        $this->Posts->expects($this->once())
            ->method('setNextToBePublished');

        $this->Posts->afterDelete(new Event(null), new Entity, new ArrayObject);
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        $this->Posts = $this->getMockForModel(get_class($this->Posts), ['setNextToBePublished']);

        $this->Posts->expects($this->once())
            ->method('setNextToBePublished');

        $this->Posts->afterSave(new Event(null), new Entity, new ArrayObject);
    }

    /**
     * Test for `beforeMarshal()` method
     * @test
     */
    public function testBeforeMarshal()
    {
        $tags = $this->Posts->newEntity($this->example)->tags;

        $this->assertInstanceOf('MeCms\Model\Entity\Tag', $tags[0]);
        $this->assertEquals('first tag', $tags[0]->tag);
        $this->assertInstanceOf('MeCms\Model\Entity\Tag', $tags[1]);
        $this->assertEquals('second tag', $tags[1]->tag);

        //In this case, the `dog` tag already exists
        $this->example['tags_as_string'] = 'first tag, dog';

        $tags = $this->Posts->newEntity($this->example)->tags;

        $this->assertInstanceOf('MeCms\Model\Entity\Tag', $tags[0]);
        $this->assertEmpty($tags[0]->id);
        $this->assertEquals('first tag', $tags[0]->tag);
        $this->assertInstanceOf('MeCms\Model\Entity\Tag', $tags[0]);
        $this->assertEquals(2, $tags[1]->id);
        $this->assertEquals('dog', $tags[1]->tag);
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $this->Posts = $this->getMockBuilder(get_class($this->Posts))
            ->setMethods(['getPreviewSize'])
            ->setConstructorArgs([[
                'table' => $this->Posts->getTable(),
                'connection' => $this->Posts->getConnection(),
            ]])
            ->getMock();

        $this->Posts->method('getPreviewSize')
            ->will($this->returnValue([400, 300]));

        //Tries with a text without images or videos
        $entity = $this->Posts->newEntity($this->example);
        $this->assertNotEmpty($this->Posts->save($entity));
        $this->assertNull($entity->preview);

        $this->Posts->delete($entity);

        //Tries with a text with an image
        $this->example['text'] = '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg' . '\' />';
        $entity = $this->Posts->newEntity($this->example);
        $this->assertNotEmpty($this->Posts->save($entity));
        $this->assertEquals(['preview', 'width', 'height'], array_keys($entity->preview));
        $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+/', $entity->preview['preview']);
        $this->assertEquals(400, $entity->preview['width']);
        $this->assertEquals(300, $entity->preview['height']);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $entity = $this->Posts->newEntity($this->example);
        $this->assertNotEmpty($this->Posts->save($entity));

        //Saves again the same entity
        $entity = $this->Posts->newEntity($this->example);
        $this->assertFalse($this->Posts->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => 'This value is already used'],
            'title' => ['_isUnique' => 'This value is already used'],
        ], $entity->getErrors());

        $entity = $this->Posts->newEntity([
            'category_id' => 999,
            'user_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ]);
        $this->assertFalse($this->Posts->save($entity));
        $this->assertEquals([
            'category_id' => ['_existsIn' => 'You have to select a valid option'],
            'user_id' => ['_existsIn' => 'You have to select a valid option'],
        ], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('posts', $this->Posts->getTable());
        $this->assertEquals('title', $this->Posts->getDisplayField());
        $this->assertEquals('id', $this->Posts->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Posts->Categories);
        $this->assertEquals('category_id', $this->Posts->Categories->getForeignKey());
        $this->assertEquals('INNER', $this->Posts->Categories->getJoinType());
        $this->assertEquals(ME_CMS . '.PostsCategories', $this->Posts->Categories->className());
        $this->assertInstanceOf('MeCms\Model\Table\PostsCategoriesTable', $this->Posts->Categories->getTarget());
        $this->assertEquals(ME_CMS . '.PostsCategories', $this->Posts->Categories->getTarget()->getRegistryAlias());
        $this->assertEquals('Categories', $this->Posts->Categories->getAlias());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Posts->Users);
        $this->assertEquals('user_id', $this->Posts->Users->getForeignKey());
        $this->assertEquals('INNER', $this->Posts->Users->getJoinType());
        $this->assertEquals(ME_CMS . '.Users', $this->Posts->Users->className());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $this->Posts->Tags);
        $this->assertEquals('post_id', $this->Posts->Tags->getForeignKey());
        $this->assertEquals('tag_id', $this->Posts->Tags->getTargetForeignKey());
        $this->assertEquals('posts_tags', $this->Posts->Tags->junction()->getTable());
        $this->assertEquals(ME_CMS . '.Tags', $this->Posts->Tags->className());
        $this->assertEquals(ME_CMS . '.PostsTags', $this->Posts->Tags->getThrough());

        $this->assertTrue($this->Posts->hasBehavior('Timestamp'));
        $this->assertTrue($this->Posts->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\PostValidator', $this->Posts->validator());
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
            $this->assertInstanceOf('MeCms\Model\Entity\Tag', $tag);
            $this->assertInstanceOf('MeCms\Model\Entity\PostsTag', $tag->_joinData);
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

        $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $post->category);
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

        $this->assertInstanceOf('MeCms\Model\Entity\User', $post->user);
        $this->assertEquals(4, $post->user->id);
    }

    /**
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $anHourAgo = time() - 3600;

        $query = $this->Posts->find();
        $this->assertInstanceOf('Cake\ORM\Query', $query);

        //Writes `next_to_be_published` and some data on cache
        Cache::write('next_to_be_published', $anHourAgo, $this->Posts->cache);
        Cache::write('someData', 'someValue', $this->Posts->cache);

        $this->assertNotEmpty(Cache::read('next_to_be_published', $this->Posts->cache));
        $this->assertNotEmpty(Cache::read('someData', $this->Posts->cache));

        //The cache will now be cleared
        $query = $this->Posts->find();
        $this->assertInstanceOf('Cake\ORM\Query', $query);

        $this->assertNotEquals($anHourAgo, Cache::read('next_to_be_published', $this->Posts->cache));
        $this->assertEmpty(Cache::read('someData', $this->Posts->cache));
    }

    /**
     * Test for `getRelated()` method
     * @test
     */
    public function testGetRelated()
    {
        //Gets a post from which to search the related posts.
        //Note that the tags of this post are sorted in ascending order
        $post = $this->Posts->findById(1)
            ->contain(['Tags' => function ($q) {
                return $q->order(['post_count' => 'ASC']);
            }])
            ->first();
        $this->assertNotEmpty($post->tags);

        $relatedPosts = $this->Posts->getRelated($post, 2, false);

        $this->assertCount(2, $relatedPosts);
        $this->assertEquals($relatedPosts, Cache::read('related_2_posts_for_1', $this->Posts->cache));

        foreach ($relatedPosts as $related) {
            $this->assertNotEmpty($related->id);
            $this->assertNotEmpty($related->title);
            $this->assertNotEmpty($related->slug);
            $this->assertNotEmpty($related->text);
            $this->assertInstanceOf('MeCms\Model\Entity\Post', $related);
        }

        //Gets related posts with image
        $related = $this->Posts->getRelated($post, 2, true);

        $this->assertCount(1, $related);
        $this->assertEquals($related, Cache::read('related_2_posts_for_1_with_images', $this->Posts->cache));

        $this->assertEquals(2, $related[0]->id);
        $this->assertNotEmpty($related[0]->title);
        $this->assertNotEmpty($related[0]->slug);
        $this->assertContains(
            '<img src="image.jpg" />Text of the second post',
            $related[0]->text
        );
        $this->assertEquals([
            'preview' => 'image.jpg',
            'width' => 400,
            'height' => 400,
        ], $related[0]->preview);
        $this->assertInstanceOf('MeCms\Model\Entity\Post', $related[0]);

        //This post has no tags
        $post = $this->Posts->findById(4)->contain(['Tags'])->first();
        $this->assertEquals([], $post->tags);
        $this->assertEquals([], $this->Posts->getRelated($post));
        $this->assertEquals([], Cache::read('related_5_posts_for_4_with_images', $this->Posts->cache));

        //This post has one tag, but this is not related to any other post
        $post = $this->Posts->findById(5)->contain(['Tags'])->first();
        $this->assertCount(1, $post->tags);
        $this->assertEquals([], $this->Posts->getRelated($post));
        $this->assertEquals([], Cache::read('related_5_posts_for_5_with_images', $this->Posts->cache));
    }

    /**
     * Test for `getRelated()` method, with an entity with no `tags` property
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage ID or tags of the post are missing
     */
    public function testGetRelatedNoTagsProperty()
    {
        $this->Posts->getRelated($this->Posts->get(1));
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['tag' => 'test'];

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.tag = :c0 AND Tags.id = (PostsTags.tag_id))', $query->sql());

        $this->assertEquals('test', $query->valueBinder()->bindings()[':c0']['value']);
    }
}
