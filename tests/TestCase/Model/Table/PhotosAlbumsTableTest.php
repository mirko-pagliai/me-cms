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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

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

        $this->PhotosAlbums = TableRegistry::get(ME_CMS . '.PhotosAlbums');

        Cache::clear(false, $this->PhotosAlbums->cache);
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
        $entity = $this->PhotosAlbums->newEntity(['title' => 'new album', 'slug' => 'new-album']);

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
        $entity = $this->PhotosAlbums->newEntity(['title' => 'new album', 'slug' => 'new-album']);

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
        $example = ['title' => 'My title', 'slug' => 'my-slug'];

        $entity = $this->PhotosAlbums->newEntity($example);
        $this->assertNotEmpty($this->PhotosAlbums->save($entity));

        //Saves again the same entity
        $entity = $this->PhotosAlbums->newEntity($example);
        $this->assertFalse($this->PhotosAlbums->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());
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
        $this->assertEquals(ME_CMS . '.Photos', $this->PhotosAlbums->Photos->className());

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
        $query = $this->PhotosAlbums->find('active');
        $this->assertStringEndsWith('FROM photos_albums PhotosAlbums INNER JOIN photos Photos ON (Photos.active = :c0 AND PhotosAlbums.id = (Photos.album_id))', $query->sql());
        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->_matchingData['Photos']->active);
        }
    }
}
