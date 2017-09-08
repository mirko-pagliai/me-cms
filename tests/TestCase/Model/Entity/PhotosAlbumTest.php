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
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Model\Entity\PhotosAlbum;
use MeTools\TestSuite\TestCase;

/**
 * PhotosAlbumTest class
 */
class PhotosAlbumTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\PhotosAlbum
     */
    protected $PhotosAlbum;

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

        $this->PhotosAlbum = new PhotosAlbum;
        $this->PhotosAlbums = TableRegistry::get(ME_CMS . '.PhotosAlbums');

        Cache::clear(false, $this->PhotosAlbums->cache);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->PhotosAlbum->isAccessible('id'));
        $this->assertFalse($this->PhotosAlbum->isAccessible('photo_count'));
        $this->assertFalse($this->PhotosAlbum->isAccessible('modified'));
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['path', 'preview'], $this->PhotosAlbum->getVirtual());
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->PhotosAlbum->id = 1;
        $this->assertEquals(PHOTOS . $this->PhotosAlbum->id, $this->PhotosAlbum->path);
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $album = $this->PhotosAlbums->findById(1)->contain('Photos')->first();
        $this->assertEquals(PHOTOS . $album->id . DS . 'photo1.jpg', $album->preview);
    }
}
