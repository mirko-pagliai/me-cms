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
 * PhotosAlbumsTableTest class
 */
class PhotosAlbumsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosAlbumsTable
     */
    protected $PhotosAlbums;

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

        $this->PhotosAlbums = TableRegistry::get('MeCms.PhotosAlbums');

        Cache::clear(false, $this->PhotosAlbums->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PhotosAlbums);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('photos', $this->PhotosAlbums->cache);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $entity = $this->PhotosAlbums->newEntity([
            'title' => 'new album',
            'slug' => 'new-album',
        ]);

        $this->assertNotEmpty($this->PhotosAlbums->save($entity));

        $this->assertFileExists($entity->path);

        //Deletes the album
        $this->assertTrue($this->PhotosAlbums->delete($entity));
        $this->assertFileNotExists($entity->path);
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        $entity = $this->PhotosAlbums->newEntity([
            'title' => 'new album',
            'slug' => 'new-album',
        ]);

        $this->assertNotEmpty($this->PhotosAlbums->save($entity));

        $this->assertFileExists($entity->path);
        $this->assertEquals('0777', substr(sprintf('%o', fileperms($entity->path)), -4));

        //@codingStandardsIgnoreLine
        @rmdir($entity->path);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = [
            'title' => 'My title',
            'slug' => 'my-slug',
        ];

        $entity = $this->PhotosAlbums->newEntity($example);
        $this->assertNotEmpty($this->PhotosAlbums->save($entity));

        //Saves again the same entity
        $entity = $this->PhotosAlbums->newEntity($example);
        $this->assertFalse($this->PhotosAlbums->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => 'This value is already used'],
            'title' => ['_isUnique' => 'This value is already used'],
        ], $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('photos_albums', $this->PhotosAlbums->getTable());
        $this->assertEquals('title', $this->PhotosAlbums->getDisplayField());
        $this->assertEquals('id', $this->PhotosAlbums->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PhotosAlbums->Photos);
        $this->assertEquals('album_id', $this->PhotosAlbums->Photos->getForeignKey());
        $this->assertEquals('MeCms.Photos', $this->PhotosAlbums->Photos->className());

        $this->assertTrue($this->PhotosAlbums->hasBehavior('Timestamp'));

        $this->assertInstanceOf('MeCms\Model\Validation\PhotosAlbumValidator', $this->PhotosAlbums->validator());
    }

    /**
     * Test for the `hasMany` association with `Photos`
     * @test
     */
    public function testHasManyPhotos()
    {
        $album = $this->PhotosAlbums->findById(1)->contain(['Photos'])->first();

        $this->assertNotEmpty($album->photos);

        foreach ($album->photos as $photo) {
            $this->assertInstanceOf('MeCms\Model\Entity\Photo', $photo);
            $this->assertEquals(1, $photo->album_id);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->PhotosAlbums->hasFinder('active'));

        $query = $this->PhotosAlbums->find('active');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals('SELECT PhotosAlbums.id AS `PhotosAlbums__id`, PhotosAlbums.title AS `PhotosAlbums__title`, PhotosAlbums.slug AS `PhotosAlbums__slug`, PhotosAlbums.description AS `PhotosAlbums__description`, PhotosAlbums.active AS `PhotosAlbums__active`, PhotosAlbums.photo_count AS `PhotosAlbums__photo_count`, PhotosAlbums.created AS `PhotosAlbums__created`, PhotosAlbums.modified AS `PhotosAlbums__modified` FROM photos_albums PhotosAlbums WHERE (PhotosAlbums.active = :c0 AND PhotosAlbums.photo_count > :c1)', $query->sql());

        $params = collection($query->valueBinder()->bindings())->extract('value')->toList();

        $this->assertEquals([
            true,
            0,
        ], $params);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $album) {
            $this->assertTrue($album->active);
            $this->assertGreaterThan(0, $album->photo_count);
        }
    }
}
