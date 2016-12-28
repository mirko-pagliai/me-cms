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
        $album = $this->PhotosAlbums->get(1);

        //Creates the dir
        //@codingStandardsIgnoreLine
        @mkdir($album->path);

        $this->assertFileExists($album->path);

        //Deletes the album
        $this->assertTrue($this->PhotosAlbums->delete($album));

        $this->assertFileNotExists($album->path);
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
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('photos_albums', $this->PhotosAlbums->table());
        $this->assertEquals('title', $this->PhotosAlbums->displayField());
        $this->assertEquals('id', $this->PhotosAlbums->primaryKey());

        $this->assertEquals('Cake\ORM\Association\HasMany', get_class($this->PhotosAlbums->Photos));
        $this->assertEquals('album_id', $this->PhotosAlbums->Photos->foreignKey());
        $this->assertEquals('MeCms.Photos', $this->PhotosAlbums->Photos->className());

        $this->assertTrue($this->PhotosAlbums->hasBehavior('Timestamp'));
    }

    /**
     * Test for the `hasMany` association with `Photos`
     * @test
     */
    public function testHasManyPhotos()
    {
        $albums = $this->PhotosAlbums->findById(1)->contain(['Photos'])->first();

        $this->assertNotEmpty($albums->photos);

        foreach ($albums->photos as $photo) {
            $this->assertEquals('MeCms\Model\Entity\Photo', get_class($photo));
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
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        $this->assertEquals(2, $query->count());

        foreach ($query->toArray() as $album) {
            $this->assertTrue($album->active);
            $this->assertGreaterThan(0, $album->photo_count);
        }
    }

    /**
     * Test for `getList()` method
     * @test
     */
    public function testGetList()
    {
        $albums = $this->PhotosAlbums->getList();
        $this->assertEquals([
            3 => 'A no active album test',
            2 => 'Another album test',
            1 => 'Test album',
        ], $albums);
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\PhotosAlbumValidator',
            get_class($this->PhotosAlbums->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
