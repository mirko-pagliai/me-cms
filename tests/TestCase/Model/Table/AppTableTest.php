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

use ArrayObject;
use BadMethodCallException;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use MeCms\TestSuite\TableTestCase;

/**
 * AppTableTest class
 */
class AppTableTest extends TableTestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosTable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Photos;

    /**
     * @var \MeCms\Model\Table\PostsTable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Posts;

    /**
     * @var \MeCms\Model\Table\PostsCategoriesTable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $PostsCategories;

    /**
     * If `true`, a mock instance of the table will be created
     * @var bool
     */
    protected $autoInitializeClass = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Photos',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Users',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        foreach (['Photos', 'Posts', 'PostsCategories'] as $table) {
            $this->$table = $this->getMockForModel('MeCms.' . $table, null);
        }
    }

    /**
     * Test for `afterDelete()` and `afterSave()` methods
     * @test
     */
    public function testAfterDeleteAndAfterSave()
    {
        foreach (['afterDelete', 'afterSave'] as $method) {
            Cache::write('testKey', 'testValue', $this->Posts->getCacheName());
            $this->Posts->$method(new Event(null), new Entity(), new ArrayObject());
            $this->assertFalse(Cache::read('testKey', $this->Posts->getCacheName()));
        }
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $example = [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Example',
            'slug' => 'example',
            'text' => 'Example text',
        ];

        $entity = $this->Posts->save($this->Posts->newEntity($example));
        $this->assertNotEmpty($entity->created);
        $this->Posts->delete($entity);

        foreach ([null, ''] as $value) {
            $entity = $this->Posts->save($this->Posts->newEntity(['created' => $value] + $example));
            $this->assertNotEmpty($entity->created);
            $this->Posts->delete($entity);
        }

        $example['created'] = new Time();
        $entity = $this->Posts->save($this->Posts->newEntity($example));
        $this->assertEquals($example['created'], $entity->created);
        $this->Posts->delete($entity);

        foreach (['2017-03-14 20:19', '2017-03-14 20:19:00'] as $value) {
            $entity = $this->Posts->save($this->Posts->newEntity(['created' => $value] + $example));
            $this->assertEquals('2017-03-14 20:19:00', $entity->created->i18nFormat('yyyy-MM-dd HH:mm:ss'));
            $this->Posts->delete($entity);
        }

        //Now tries with a record that already exists
        $entity = $this->Posts->get(1);
        foreach ([null, ''] as $value) {
            $entity = $this->Posts->save($entity->set('created', $value));
            $this->assertNotEmpty($entity->created);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Posts->find('active');
        $this->assertStringEndsWith('FROM posts Posts WHERE (Posts.active = :c0 AND Posts.created <= :c1)', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(Time::class, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());
        foreach ($query as $entity) {
            $this->assertTrue($entity->active && !$entity->created->isFuture());
        }
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPending()
    {
        $query = $this->Posts->find('pending');
        $this->assertStringEndsWith('FROM posts Posts WHERE (Posts.active = :c0 OR Posts.created > :c1)', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(Time::class, $query->getValueBinder()->bindings()[':c1']['value']);
        foreach ($query as $entity) {
            $this->assertTrue(!$entity->active || $entity->created->isFuture());
        }
    }

    /**
     * Test for `findRandom()` method
     * @test
     */
    public function testFindRandom()
    {
        $query = $this->Posts->find('random');
        $this->assertStringEndsWith('FROM posts Posts ORDER BY rand() LIMIT 1', $query->sql());

        $query = $this->Posts->find('random')->limit(2);
        $this->assertStringEndsWith('FROM posts Posts ORDER BY rand() LIMIT 2', $query->sql());
    }

    /**
     * Test for `getCacheName()` method
     * @test
     */
    public function testGetCacheName()
    {
        $this->assertEquals('posts', $this->Posts->getCacheName());
        $this->assertEquals(['posts', 'users'], $this->Posts->getCacheName(true));
    }

    /**
     * Test for `getList()` method
     * @test
     */
    public function testGetList()
    {
        $expected = [
            1 => 'photo1.jpg',
            3 => 'photo3.jpg',
            4 => 'photo4.jpg',
            2 => 'photoa.jpg',
        ];
        $query = $this->Photos->getList();
        $this->assertStringEndsWith('ORDER BY ' . $this->Photos->getDisplayField() . ' ASC', $query->sql());
        $this->assertEquals($expected, $query->toArray());
        $fromCache = Cache::read('photos_list', $this->Photos->getCacheName())->toArray();
        $this->assertEquals($query->toArray(), $fromCache);
    }

    /**
     * Test for `getTreeList()` method
     * @test
     */
    public function testGetTreeList()
    {
        $expected = [
            1 => 'First post category',
            3 => '—Sub post category',
            4 => '——Sub sub post category',
            2 => 'Another post category',
        ];
        $query = $this->PostsCategories->getTreeList();
        $this->assertStringEndsNotWith('ORDER BY ' . $this->PostsCategories->getDisplayField() . ' ASC', $query->sql());
        $this->assertEquals($expected, $query->toArray());
        $fromCache = Cache::read('posts_categories_tree_list', $this->PostsCategories->getCacheName())->toArray();
        $this->assertEquals($query->toArray(), $fromCache);

        //With a model that does not have a tree
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown finder method "treeList"');
        $this->Posts->getTreeList();
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = [
            'id' => 2,
            'title' => 'Title',
            'user' => 3,
            'category' => 4,
            'active' => I18N_YES,
            'priority' => 3,
            'created' => '2016-12',
        ];
        $expectedSql = 'FROM posts Posts WHERE (Posts.id = :c0 AND Posts.title like :c1 AND Posts.user_id = :c2 AND Posts.category_id = :c3 AND Posts.active = :c4 AND Posts.priority = :c5 AND Posts.created >= :c6 AND Posts.created < :c7)';
        $expectedParams = [
            2,
            '%Title%',
            3,
            4,
            true,
            3,
            '2016-12-01 00:00:00',
            '2017-01-01 00:00:00',
        ];
        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertStringEndsWith($expectedSql, $query->sql());

        $params = collection($query->getValueBinder()->bindings())->extract('value')->map(function ($value) {
            return $value instanceof Time ? $value->i18nFormat('yyyy-MM-dd HH:mm:ss') : $value;
        })->toList();
        $this->assertEquals($expectedParams, $params);

        $query = $this->Posts->queryFromFilter($this->Posts->find(), ['active' => I18N_NO] + $data);
        $this->assertStringEndsWith($expectedSql, $query->sql());
        $this->assertEquals(false, $query->getValueBinder()->bindings()[':c4']['value']);

        $query = $this->Photos->queryFromFilter($this->Photos->find(), $data = ['filename' => 'image.jpg']);
        $this->assertStringEndsWith('FROM photos Photos WHERE Photos.filename like :c0', $query->sql());
        $this->assertEquals('%image.jpg%', $query->getValueBinder()->bindings()[':c0']['value']);

        //With some invalid datas
        $query = $this->Posts->queryFromFilter($this->Posts->find(), ['title' => 'ab', 'priority' => 6, 'created' => '2016-12-30']);
        $this->assertEmpty($query->getValueBinder()->bindings());

        $query = $this->Photos->queryFromFilter($this->Photos->find(), ['filename' => 'ab']);
        $this->assertEmpty($query->getValueBinder()->bindings());
    }
}
