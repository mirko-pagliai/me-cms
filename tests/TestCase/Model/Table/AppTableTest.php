<?php
/** @noinspection PhpDocMissingThrowsInspection */
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

use App\Model\Table\ArticlesTable;
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
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::afterSave()
     */
    public function testEventMethods(): void
    {
        /** @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject $PostsTable */
        $PostsTable = $this->getMockForModel('MeCms.Posts', ['clearCache']);
        $PostsTable->expects($this->once())->method('clearCache');
        $PostsTable->save($PostsTable->get(1)->set('title', 'New title'));
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::afterDelete()
     */
    public function testAfterDeleteEventMethod(): void
    {
        /** @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject $PostsTable */
        $PostsTable = $this->getMockForModel('MeCms.Posts', ['clearCache']);
        $PostsTable->expects($this->once())->method('clearCache');
        $PostsTable->delete($PostsTable->get(1));
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::clearCache()
     */
    public function testClearCache(): void
    {
        Cache::write('testKey', 'testValue', $this->Posts->getCacheName());
        Cache::write('associatedTestKey', 'associatedTestValue', $this->Posts->Users->getCacheName());
        $this->assertTrue($this->Posts->clearCache());
        $this->assertNull(Cache::read('testKey', $this->Posts->getCacheName()));
        $this->assertNull(Cache::read('associatedTestKey', $this->Posts->Users->getCacheName()));
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::deleteAll()
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
     * @test
     * @uses \MeCms\Model\Table\AppTable::findActive()
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
     * @test
     * @uses \MeCms\Model\Table\AppTable::findPending()
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
     * @test
     * @uses \MeCms\Model\Table\AppTable::findRandom()
     */
    public function testFindRandomMethod(): void
    {
        $query = $this->Posts->find('random');
        $this->assertSqlEndsWith('FROM posts Posts ORDER BY rand() LIMIT 1', $query->sql());

        $query = $this->Posts->find('random')->limit(2);
        $this->assertSqlEndsWith('FROM posts Posts ORDER BY rand() LIMIT 2', $query->sql());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::getCacheName()
     */
    public function testGetCacheName(): void
    {
        $this->assertSame('', $this->getTable('ArticlesTable', ['className' => ArticlesTable::class])->getCacheName());
        $this->assertSame('posts', $this->Posts->getCacheName());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::getCacheNameWithAssociated()
     */
    public function testGetCacheNameWithAssociated(): void
    {
        $this->assertSame([], $this->getTable('ArticlesTable', ['className' => ArticlesTable::class])->getCacheNameWithAssociated());
        $this->assertSame(['posts', 'users'], $this->Posts->getCacheNameWithAssociated());
        $this->assertSame(['users', 'posts'], $this->getTable('MeCms.Users')->getCacheNameWithAssociated());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::getList()
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
     * @test
     * @uses \MeCms\Model\Table\AppTable::getTreeList()
     */
    public function testGetTreeList(): void
    {
        $expected = [
            1 => 'First post category',
            3 => '—Sub post category',
            4 => '——Sub sub post category',
            2 => 'Another post category',
        ];
        $query = $this->Posts->Categories->getTreeList();
        $this->assertSqlEndsNotWith('ORDER BY `' . $this->Posts->Categories->getDisplayField() . '` ASC', $query->sql());
        $this->assertEquals($expected, $query->toArray());
        $fromCache = Cache::read('posts_categories_tree_list', $this->Posts->Categories->getCacheName())->toArray();
        $this->assertEquals($query->toArray(), $fromCache);

        //On failure, With a model that does not have a tree
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown finder method "treeList"');
        $this->Posts->getTreeList();
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::query()
     */
    public function testQuery(): void
    {
        $this->assertInstanceOf(Query::class, $this->Posts->query());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
     */
    public function testQueryFromFilter(): void
    {
        $expectedSql = 'FROM posts Posts WHERE (Posts.id = :c0 AND (Posts.title like :c1 OR Posts.slug like :c2) AND Posts.user_id = :c3 AND Posts.category_id = :c4 AND Posts.active = :c5 AND Posts.priority = :c6 AND Posts.created >= :c7 AND Posts.created < :c8)';
        $expectedParams = [
            2,
            '%Title%',
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
        $this->assertFalse($query->getValueBinder()->bindings()[':c5']['value']);

        //With some invalid data
        $query = $this->Posts->queryFromFilter($this->Posts->find(), ['title' => 'ab', 'priority' => 6, 'created' => '2016-12-30']);
        $this->assertEmpty($query->getValueBinder()->bindings());

        /**
         * This table schema without the `slug` column
         * @var \MeCms\Model\Table\UsersTable $Users
         */
        $Users = $this->getTable('MeCms.Users');
        $query = $Users->queryFromFilter($Users->find(), $data);
        $result = $query->sql();
        $this->assertStringContainsString('AND `Users`.`title` like :c1', $result);
        $this->assertStringNotContainsString('slug', $result);
    }
}
