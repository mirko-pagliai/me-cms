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
 * PhotosTableTest class
 */
class PhotosTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosTable
     */
    protected $Photos;

    /**
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.photos',
        'plugin.me_cms.photos_albums',
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

        $this->example = [
            'album_id' => 1,
            'filename' => 'pic.jpg',
        ];

        $file = PHOTOS . $this->example['album_id'] . DS . $this->example['filename'];

        //Creates the file for the example
        //@codingStandardsIgnoreStart
        @mkdir(dirname($file));
        @copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);
        //@codingStandardsIgnoreEnd

        Cache::clear(false, $this->Photos->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes the file for the example
        //@codingStandardsIgnoreLine
        @unlink(PHOTOS . $this->example['album_id'] . DS . $this->example['filename']);

        unset($this->Photos);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('photos', $this->Photos->cache);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $entity = $this->Photos->get(1);

        $this->assertFileExists($entity->path);

        //Deletes
        $this->assertTrue($this->Photos->delete($entity));
        $this->assertFileNotExists($entity->path);
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $entity = $this->Photos->newEntity($this->example);
        $this->assertNotEmpty($this->Photos->save($entity));

        $this->assertEquals([
            'width' => 400,
            'height' => 400,
        ], $entity->size);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $entity = $this->Photos->newEntity($this->example);
        $this->assertNotEmpty($this->Photos->save($entity));

        //Saves again the same entity
        $entity = $this->Photos->newEntity($this->example);
        $this->assertFalse($this->Photos->save($entity));
        $this->assertEquals(['filename' => ['_isUnique' => 'This value is already used']], $entity->errors());

        $entity = $this->Photos->newEntity([
            'album_id' => 999,
            'filename' => 'pic2.jpg',
        ]);
        $this->assertFalse($this->Photos->save($entity));
        $this->assertEquals(['album_id' => ['_existsIn' => 'You have to select a valid option']], $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('photos', $this->Photos->getTable());
        $this->assertEquals('filename', $this->Photos->getDisplayField());
        $this->assertEquals('id', $this->Photos->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Photos->Albums);
        $this->assertEquals('album_id', $this->Photos->Albums->getForeignKey());
        $this->assertEquals('INNER', $this->Photos->Albums->getJoinType());
        $this->assertEquals('MeCms.PhotosAlbums', $this->Photos->Albums->className());

        $this->assertTrue($this->Photos->hasBehavior('Timestamp'));
        $this->assertTrue($this->Photos->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\PhotoValidator', $this->Photos->validator());
    }

    /**
     * Test for the `belongsTo` association with `PhotosAlbums`
     * @test
     */
    public function testBelongsToPhotosAlbums()
    {
        $photo = $this->Photos->findById(2)->contain(['Albums'])->first();

        $this->assertNotEmpty($photo->album);

        $this->assertInstanceOf('MeCms\Model\Entity\PhotosAlbum', $photo->album);
        $this->assertEquals(2, $photo->album->id);
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Photos->find('active')->contain(['Albums']);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM photos Photos INNER JOIN photos_albums Albums ON Albums.id = (Photos.album_id) WHERE (Photos.active = :c0 AND Albums.active = :c1)', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertTrue($query->valueBinder()->bindings()[':c1']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->active);
            $this->assertTrue($entity->album->active);
        }
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPending()
    {
        $query = $this->Photos->find('pending');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM photos Photos INNER JOIN photos_albums Albums ON Albums.id = (Photos.album_id) WHERE (Albums.active = :c0 OR Photos.active = :c1)', $query->sql());

        $this->assertFalse($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->valueBinder()->bindings()[':c1']['value']);

        $this->assertEquals([4, 5], collection($query->toArray())->extract('id')->toList());
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['album' => 2];

        $query = $this->Photos->queryFromFilter($this->Photos->find(), $data);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM photos Photos WHERE Photos.album_id = :c0', $query->sql());

        $this->assertEquals(2, $query->valueBinder()->bindings()[':c0']['value']);
    }
}
