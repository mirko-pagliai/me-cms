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

        Cache::clear(false, $this->Photos->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

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
        $photo = $this->Photos->get(1);

        //Creates the photo
        //@codingStandardsIgnoreLine
        @mkdir(dirname($photo->path));
        file_put_contents($photo->path, null);

        $this->assertFileExists($photo->path);

        //Deletes the photos
        $this->assertTrue($this->Photos->delete($photo));
        $this->assertFileNotExists($photo->path);

        //@codingStandardsIgnoreLine
        @rmdir(dirname($photo->path));
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('photos', $this->Photos->table());
        $this->assertEquals('filename', $this->Photos->displayField());
        $this->assertEquals('id', $this->Photos->primaryKey());

        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->Photos->Albums));
        $this->assertEquals('album_id', $this->Photos->Albums->foreignKey());
        $this->assertEquals('INNER', $this->Photos->Albums->joinType());
        $this->assertEquals('MeCms.PhotosAlbums', $this->Photos->Albums->className());

        $this->assertTrue($this->Photos->hasBehavior('Timestamp'));
        $this->assertTrue($this->Photos->hasBehavior('CounterCache'));
    }

    /**
     * Test for the `belongsTo` association with `PhotosAlbums`
     * @test
     */
    public function testBelongsToPhotosAlbums()
    {
        $photo = $this->Photos->findById(2)->contain(['Albums'])->first();

        $this->assertNotEmpty($photo->album);

        $this->assertEquals('MeCms\Model\Entity\PhotosAlbum', get_class($photo->album));
        $this->assertEquals(2, $photo->album->id);
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->Photos->hasFinder('active'));

        $query = $this->Photos->find('active')->contain(['Albums']);
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Photos.id AS `Photos__id`, Photos.album_id AS `Photos__album_id`, Photos.filename AS `Photos__filename`, Photos.description AS `Photos__description`, Photos.active AS `Photos__active`, Photos.created AS `Photos__created`, Photos.modified AS `Photos__modified`, Albums.id AS `Albums__id`, Albums.title AS `Albums__title`, Albums.slug AS `Albums__slug`, Albums.description AS `Albums__description`, Albums.active AS `Albums__active`, Albums.photo_count AS `Albums__photo_count`, Albums.created AS `Albums__created`, Albums.modified AS `Albums__modified` FROM photos Photos INNER JOIN photos_albums Albums ON (Albums.active = :c0 AND Albums.id = (Photos.album_id)) WHERE Photos.active = :c1', $query->sql());

        $params = array_map(function ($v) {
            return $v['value'];
        }, $query->valueBinder()->bindings());

        $this->assertEquals([
            ':c0' => true,
            ':c1' => true,
        ], $params);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $photo) {
            $this->assertTrue($photo->active);
            $this->assertTrue($photo->album->active);
        }
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['album' => 2];

        $query = $this->Photos->queryFromFilter($this->Photos->find(), $data);
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Photos.id AS `Photos__id`, Photos.album_id AS `Photos__album_id`, Photos.filename AS `Photos__filename`, Photos.description AS `Photos__description`, Photos.active AS `Photos__active`, Photos.created AS `Photos__created`, Photos.modified AS `Photos__modified` FROM photos Photos WHERE Photos.album_id = :c0', $query->sql());

        $this->assertEquals(2, $query->valueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\PhotoValidator',
            get_class($this->Photos->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
