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

use Cake\Cache\Cache;
use Cake\Collection\CollectionInterface;
use Cake\I18n\Time;
use Cake\Utility\Hash;
use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\PostsTag;
use MeCms\Model\Entity\Tag;
use MeCms\Model\Validation\PostValidator;
use MeCms\TestSuite\PostsAndPagesTablesTestCase;
use Tools\Exception\PropertyNotExistsException;

/**
 * PostsTableTest class
 */
class PostsTableTest extends PostsAndPagesTablesTestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
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
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
        'plugin.MeCms.Users',
    ];

    /**
     * Called once before test methods in a case are started
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$example += ['user_id' => 1, 'tags_as_string' => 'first tag, second tag'];
    }

    /**
     * Test for event methods
     * @test
     */
    public function testEventMethods(): void
    {
        parent::testEventMethods();

        $tags = $this->Table->newEntity(self::$example)->get('tags');
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        $this->assertEquals(['first tag', 'second tag'], Hash::extract($tags, '{n}.tag'));

        //In this case, the `dog` tag already exists
        self::$example['tags_as_string'] = 'first tag, dog';
        $tags = $this->Table->newEntity(self::$example)->get('tags');
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        $this->assertEquals([2], Hash::extract($tags, '{n}.id'));
        $this->assertEquals(['first tag', 'dog'], Hash::extract($tags, '{n}.tag'));
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules(): void
    {
        parent::testBuildRules();

        $entity = $this->Table->newEntity([
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'user_id' => 999,
        ] + self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['user_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize(): void
    {
        parent::testInitialize();

        $this->assertEquals('posts', $this->Table->getTable());

        $this->assertBelongsTo($this->Table->Users);
        $this->assertEquals('user_id', $this->Table->Users->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Users->getJoinType());

        $this->assertBelongsToMany($this->Table->Tags);
        $this->assertEquals('post_id', $this->Table->Tags->getForeignKey());
        $this->assertEquals('tag_id', $this->Table->Tags->getTargetForeignKey());
        $this->assertEquals('posts_tags', $this->Table->Tags->junction()->getTable());

        $this->assertInstanceOf(PostValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for associations
     * @test
     */
    public function testAssociations(): void
    {
        $tags = $this->Table->findById(2)->contain('Tags')->extract('tags')->first();
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        foreach ($tags as $tag) {
            $this->assertInstanceOf(PostsTag::class, $tag->_joinData);
            $this->assertEquals(2, $tag->_joinData->get('post_id'));
        }
    }

    /**
     * Test for `find()` methods
     * @test
     */
    public function testFindMethods(): void
    {
        $query = $this->Table->find('forIndex');
        $sql = $query->sql();
        $this->assertArrayKeysEqual(['Categories', 'Tags', 'Users'], $query->getContain());
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_categories Categories ON Categories.id = (Posts.category_id) INNER JOIN users Users ON Users.id = (Posts.user_id) ORDER BY Posts.created DESC', $sql);
    }

    /**
     * Test for `getRelated()` method
     * @test
     */
    public function testGetRelated(): void
    {
        $this->loadFixtures();

        //Gets a post from which to search the related posts.
        //Note that the tags of this post are sorted in ascending order
        $post = $this->Table->findById(1)->contain(['Tags' => ['sort' => ['post_count' => 'ASC']]])->first();
        $this->assertNotEmpty($post->get('tags'));

        $related = $this->Table->getRelated($post, 2, false);
        $this->assertInstanceOf(CollectionInterface::class, $related);
        $this->assertCount(2, $related);
        $this->assertEquals($related, Cache::read('related_2_posts_for_1', $this->Table->getCacheName()));
        $this->assertContainsOnlyInstancesOf(Post::class, $related);

        //Gets related posts with image
        $related = $this->Table->getRelated($post, 2, true);
        $this->assertInstanceOf(CollectionInterface::class, $related);
        $this->assertCount(1, $related);
        $this->assertEquals($related, Cache::read('related_2_posts_for_1_with_images', $this->Table->getCacheName()));
        $firstRelated = $related->first();
        $this->assertInstanceOf(Post::class, $firstRelated);
        $this->assertEquals(2, $firstRelated->get('id'));
        $this->assertCount(1, $firstRelated->get('preview'));
        $this->assertEquals('image.jpg', array_value_first($firstRelated->get('preview'))->get('url'));
        $this->assertEquals(400, array_value_first($firstRelated->get('preview'))->get('width'));
        $this->assertEquals(400, array_value_first($firstRelated->get('preview'))->get('height'));

        //This post has no tags
        $post = $this->Table->findById(4)->contain('Tags')->first();
        $this->assertEmpty($post->get('tags'));
        $this->assertTrue($this->Table->getRelated($post)->isEmpty());
        $this->assertTrue(Cache::read('related_5_posts_for_4_with_images', $this->Table->getCacheName())->isEmpty());

        //This post has one tag, but this is not related to any other post
        $post = $this->Table->findById(5)->contain('Tags')->first();
        $this->assertCount(1, $post->get('tags'));
        $this->assertTrue($this->Table->getRelated($post)->isEmpty());
        $this->assertTrue(Cache::read('related_5_posts_for_5_with_images', $this->Table->getCacheName())->isEmpty());

        //With an entity with no `tags` property
        $this->expectException(PropertyNotExistsException::class);
        $this->Table->getRelated($this->Table->get(1));
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter(): void
    {
        $query = $this->Table->queryFromFilter($this->Table->find(), ['tag' => 'test']);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (tag = :c0 AND Tags.id = (PostsTags.tag_id))', $query->sql());
        $this->assertEquals('test', $query->getValueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `queryForRelated()` method
     * @test
     */
    public function testQueryForRelated(): void
    {
        $this->loadFixtures();

        $query = $this->Table->queryForRelated(4, true);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.id = :c0 AND Tags.id = (PostsTags.tag_id)) WHERE (Posts.active = :c1 AND Posts.created <= :c2 AND (Posts.preview) IS NOT NULL AND Posts.preview != :c3)', $query->sql());
        $this->assertEquals(4, $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(true, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertInstanceof(Time::class, $query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertEquals([], $query->getValueBinder()->bindings()[':c3']['value']);

        $query = $this->Table->queryForRelated(4, false);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.id = :c0 AND Tags.id = (PostsTags.tag_id)) WHERE (Posts.active = :c1 AND Posts.created <= :c2)', $query->sql());
        $this->assertEquals(4, $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(true, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertInstanceof(Time::class, $query->getValueBinder()->bindings()[':c2']['value']);
    }
}
