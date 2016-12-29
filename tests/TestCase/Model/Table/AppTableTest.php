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
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * AppTableTest class
 */
class AppTableTest extends TestCase
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
        'plugin.me_cms.photos',
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
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

        $this->Photos = TableRegistry::get('MeCms.Photos');
        $this->Posts = TableRegistry::get('MeCms.Posts');

        Cache::clear(false, $this->Photos->cache);
        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Photos, $this->Posts);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        //Writes something on cache
        Cache::write('testKey', 'testValue', $this->Posts->cache);
        $this->assertEquals('testValue', Cache::read('testKey', $this->Posts->cache));

        $this->Posts->afterDelete(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);

        //The cache is cleared
        $this->assertFalse(Cache::read('testKey', $this->Posts->cache));
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        //Writes something on cache
        Cache::write('testKey', 'testValue', $this->Posts->cache);
        $this->assertEquals('testValue', Cache::read('testKey', $this->Posts->cache));

        $this->Posts->afterSave(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);

        //The cache is cleared
        $this->assertFalse(Cache::read('testKey', $this->Posts->cache));
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->Posts->hasFinder('active'));

        $query = $this->Posts->find('active');

        $this->assertEquals(3, $query->count());

        foreach ($query->toArray() as $post) {
            $this->assertTrue($post->active);
            $this->assertTrue(!$post->created->isFuture());
        }
    }

    public function testFindRandom()
    {
        $this->assertTrue($this->Posts->hasFinder('random'));

        $query = $this->Posts->find('random');

        $this->assertEquals('SELECT Posts.id AS `Posts__id`, Posts.category_id AS `Posts__category_id`, Posts.user_id AS `Posts__user_id`, Posts.title AS `Posts__title`, Posts.slug AS `Posts__slug`, Posts.subtitle AS `Posts__subtitle`, Posts.text AS `Posts__text`, Posts.priority AS `Posts__priority`, Posts.created AS `Posts__created`, Posts.modified AS `Posts__modified`, Posts.active AS `Posts__active` FROM posts Posts ORDER BY rand() LIMIT 1', $query->sql());

        $query = $this->Posts->find('random')->limit(2);

        $this->assertEquals('SELECT Posts.id AS `Posts__id`, Posts.category_id AS `Posts__category_id`, Posts.user_id AS `Posts__user_id`, Posts.title AS `Posts__title`, Posts.slug AS `Posts__slug`, Posts.subtitle AS `Posts__subtitle`, Posts.text AS `Posts__text`, Posts.priority AS `Posts__priority`, Posts.created AS `Posts__created`, Posts.modified AS `Posts__modified`, Posts.active AS `Posts__active` FROM posts Posts ORDER BY rand() LIMIT 2', $query->sql());
    }

    /**
     * Test for `getNextToBePublished()` and `setNextToBePublished()` methods
     * @test
     */
    public function testGetNextToBePublishedAndSetNextToBePublished()
    {
        $this->assertFalse($this->Posts->getNextToBePublished());
        $this->assertFalse($this->Posts->setNextToBePublished());

        //Creates a record with a future publication time (1 hours)
        $created = new Time('+1 hours');

        $entity = $this->Posts->newEntity([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Future record',
            'slug' => 'future-record',
            'text' => 'Example text',
            'created' => $created,
        ]);

        $this->assertNotEmpty($this->Posts->save($entity));

        $this->assertEquals($created->toUnixString(), $this->Posts->setNextToBePublished());
        $this->assertEquals($created->toUnixString(), $this->Posts->getNextToBePublished());

        //Creates another record with a future publication time (30 minuts)
        //This record takes precedence over the previous
        $created = new Time('+30 minutes');

        $entity = $this->Posts->newEntity([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Another future record',
            'slug' => 'another-future-record',
            'text' => 'Example text',
            'created' => $created,
        ]);

        $this->assertNotEmpty($this->Posts->save($entity));

        $this->assertEquals($created->toUnixString(), $this->Posts->setNextToBePublished());
        $this->assertEquals($created->toUnixString(), $this->Posts->getNextToBePublished());
    }

    /**
     * Test for `isOwnedBy()` method
     * @test
     */
    public function testIsOwnedBy()
    {
        $this->assertTrue($this->Posts->isOwnedBy(2, 4));
        $this->assertFalse($this->Posts->isOwnedBy(2, 1));
        $this->assertTrue($this->Posts->isOwnedBy(1, 1));
        $this->assertFalse($this->Posts->isOwnedBy(1, 2));
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
            'active' => 'yes',
            'priority' => 3,
            'created' => '2016-12',
        ];

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        $this->assertEquals('SELECT Posts.id AS `Posts__id`, Posts.category_id AS `Posts__category_id`, Posts.user_id AS `Posts__user_id`, Posts.title AS `Posts__title`, Posts.slug AS `Posts__slug`, Posts.subtitle AS `Posts__subtitle`, Posts.text AS `Posts__text`, Posts.priority AS `Posts__priority`, Posts.created AS `Posts__created`, Posts.modified AS `Posts__modified`, Posts.active AS `Posts__active` FROM posts Posts WHERE (Posts.id = :c0 AND Posts.title like :c1 AND Posts.user_id = :c2 AND Posts.category_id = :c3 AND Posts.active = :c4 AND Posts.priority = :c5 AND Posts.created >= :c6 AND Posts.created < :c7)', $query->sql());

        $params = array_map(function ($v) {
            if (is_object($v['value']) && get_class($v['value']) === 'Cake\I18n\Time') {
                return ($v['value']->nice());
            }

            return $v['value'];
        }, $query->valueBinder()->bindings());

        $this->assertEquals([
            ':c0' => 2,
            ':c1' => '%Title%',
            ':c2' => 3,
            ':c3' => 4,
            ':c4' => true,
            ':c5' => 3,
            ':c6' => 'Dec 1, 2016, 12:00 AM',
            ':c7' => 'Jan 1, 2017, 12:00 AM',
        ], $params);

        $data['active'] = 'no';

        $query = $this->Posts->queryFromFilter($this->Posts->find(), $data);
        $query->execute();

        $this->assertEquals(false, $query->valueBinder()->bindings()[':c4']['value']);

        $data = ['filename' => 'image.jpg'];

        $query = $this->Photos->queryFromFilter($this->Photos->find(), $data);
        $this->assertEquals('SELECT Photos.id AS `Photos__id`, Photos.album_id AS `Photos__album_id`, Photos.filename AS `Photos__filename`, Photos.description AS `Photos__description`, Photos.active AS `Photos__active`, Photos.created AS `Photos__created`, Photos.modified AS `Photos__modified` FROM photos Photos WHERE Photos.filename like :c0', $query->sql());

        $this->assertEquals('%image.jpg%', $query->valueBinder()->bindings()[':c0']['value']);
    }
}
