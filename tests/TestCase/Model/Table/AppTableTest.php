<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

use BadMethodCallException;
use Cake\Cache\Cache;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18nDateTimeInterface;
use MeCms\ORM\Query;
use MeCms\TestSuite\TableTestCase;

/**
 * AppTableTest class
 */
class AppTableTest extends TableTestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * @var \MeCms\Model\Table\PostsCategoriesTable
     */
    protected $PostsCategories;

    /**
     * If `true`, a mock instance of the table will be created
     * @var bool
     */
    protected bool $autoInitializeClass = false;

    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Users',
    ];

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->Posts ??= $this->getTable('MeCms.Posts');
        $this->PostsCategories ??= $this->getTable('MeCms.PostsCategories');
    }

    /**
     * Test for event methods
     * @test
     */
    public function testEventMethods(): void
    {
        $example = [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Example',
            'slug' => 'example',
            'text' => 'Example text',
        ];

        /** @var \MeCms\Model\Table\AppTable&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('MeCms.Posts', ['clearCache']);
        $Table->expects($this->atLeast(2))->method('clearCache');

        /** @var \Cake\Datasource\EntityInterface $entity */
        $entity = $Table->save($Table->newEntity($example));
        $this->assertNotEmpty($entity->get('created'));
        $Table->delete($entity);

        $now = new FrozenTime();
        /** @var \Cake\Datasource\EntityInterface $entity */
        $entity = $Table->save($Table->newEntity(['created' => $now] + $example));
        $this->assertEquals($now, $entity->get('created'));
        $Table->delete($entity);

        foreach (['2017-03-14 20:19', '2017-03-14 20:19:00'] as $created) {
            /** @var \Cake\Datasource\EntityInterface $entity */
            $entity = $Table->save($Table->newEntity(compact('created') + $example));
            $this->assertEquals('2017-03-14 20:19:00', $entity->get('created')->i18nFormat('yyyy-MM-dd HH:mm:ss'));
            $Table->delete($entity);
        }
    }

    /**
     * Test for `clearCache()` method
     * @test
     */
    public function testClearCache(): void
    {
        Cache::write('testKey', 'testValue', $this->Posts->getCacheName());
        $this->assertTrue($this->Posts->clearCache());
        $this->assertNull(Cache::read('testKey', $this->Posts->getCacheName()));
    }

    /**
     * Test for `deleteAll()` method
     * @test
     */
    public function testDeleteAll(): void
    {
        $this->assertNotEmpty($this->Posts->find()->count());
        Cache::write('testKey', 'testValue', $this->Posts->getCacheName());
        $this->assertGreaterThan(0, $this->Posts->deleteAll(['id IS NOT' => null]));
        $this->assertEmpty($this->Posts->find()->count());
        $this->assertNull(Cache::read('testKey', $this->Posts->getCacheName()));
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActiveMethod(): void
    {
        $query = $this->Posts->find('active');
        $this->assertNotEmpty($query->count());
        foreach ($query as $entity) {
            $this->assertTrue($entity->get('active') && !$entity->get('created')->isFuture());
        }

        $this->assertSqlEndsWith('FROM posts Posts WHERE (Posts.active = :c0 AND Posts.created <= :c1)', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(FrozenTime::class, $query->getValueBinder()->bindings()[':c1']['value']);
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPendingMethod(): void
    {
        $query = $this->Posts->find('pending');
        $this->assertNotEmpty($query->count());
        foreach ($query as $entity) {
            $this->assertTrue(!$entity->get('active') || $entity->get('created')->isFuture());
        }

        $this->assertSqlEndsWith('FROM posts Posts WHERE (Posts.active = :c0 OR Posts.created > :c1)', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(FrozenTime::class, $query->getValueBinder()->bindings()[':c1']['value']);
    }

    /**
     * Test for `findRandom()` method
     * @test
     */
    public function testFindRandomMethod(): void
    {
        $query = $this->Posts->find('random');
        $this->assertSqlEndsWith('FROM posts Posts ORDER BY rand() LIMIT 1', $query->sql());

        $query = $this->Posts->find('random')->limit(2);
        $this->assertSqlEndsWith('FROM posts Posts ORDER BY rand() LIMIT 2', $query->sql());
    }

    /**
     * Test for `getCacheName()` method
     * @test
     */
    public function testGetCacheName(): void
    {
        $this->assertEquals('posts', $this->Posts->getCacheName());
        $this->assertEquals(['posts', 'users'], $this->Posts->getCacheName(true));
    }

    /**
     * Test for `getList()` method
     * @test
     */
    public function testGetList(): void
    {
        $query = $this->Posts->getList();
        $this->assertNotEmpty($query->toArray());
        $fromCache = Cache::read('posts_list', $this->Posts->getCacheName())->toArray();
        $this->assertEquals($query->toArray(), $fromCache);
        $this->assertSqlEndsWith('ORDER BY ' . $this->Posts->getDisplayField() . ' ASC', $query->sql());
    }

    /**
     * Test for `getTreeList()` method
     * @test
     */
    public function testGetTreeList(): void
    {
        $expected = [
            1 => 'First post category',
            3 => '—Sub post category',
            4 => '——Sub sub post category',
            2 => 'Another post category',
        ];
        $query = $this->PostsCategories->getTreeList();
        $this->assertSqlEndsNotWith('ORDER BY `' . $this->PostsCategories->getDisplayField() . '` ASC', $query->sql());
        $this->assertEquals($expected, $query->toArray());
        $fromCache = Cache::read('posts_categories_tree_list', $this->PostsCategories->getCacheName())->toArray();
        $this->assertEquals($query->toArray(), $fromCache);
    }

    /**
     * Test for `getTreeList()` method
     * @test
     */
    public function testGetTreeListOnFailure(): void
    {
        //With a model that does not have a tree
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown finder method "treeList"');
        $this->Posts->getTreeList();
    }

    /**
     * Test for `query()` method
     * @test
     */
    public function testQuery(): void
    {
        $this->assertInstanceOf(Query::class, $this->Posts->query());
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter(): void
    {
        $expectedSql = 'FROM posts Posts WHERE (Posts.id = :c0 AND Posts.title like :c1 AND Posts.user_id = :c2 AND Posts.category_id = :c3 AND Posts.active = :c4 AND Posts.priority = :c5 AND Posts.created >= :c6 AND Posts.created < :c7)';
        $expectedParams = [
            2,
            '%Title%',
            3,
            4,
            true,
            3,
            '12/1/16, 12:00 AM',
            '1/1/17, 12:00 AM',
        ];
        $data = [
            'id' => 2,
            'title' => 'Title',
            'user' => 3,
            'category' => 4,
            'active' => I18N_YES,
            'priority' => 3,
            'created' => '2016-12',
        ];
        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertSqlEndsWith($expectedSql, $query->sql());

        $params = array_map(fn($value) => $value instanceof I18nDateTimeInterface ? $value->i18nFormat() : $value, collection($query->getValueBinder()->bindings())->extract('value')->toList());
        $this->assertEquals($expectedParams, $params);

        $query = $this->Posts->queryFromFilter($this->Posts->find(), ['active' => I18N_NO] + $data);
        $this->assertSqlEndsWith($expectedSql, $query->sql());
        $this->assertEquals(false, $query->getValueBinder()->bindings()[':c4']['value']);

        //With some invalid data
        $query = $this->Posts->queryFromFilter($this->Posts->find(), ['title' => 'ab', 'priority' => 6, 'created' => '2016-12-30']);
        $this->assertEmpty($query->getValueBinder()->bindings());
    }
}
