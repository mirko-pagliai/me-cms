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

use Cake\TestSuite\TestCase;
use MeCms\Model\Entity\PhotosAlbum;

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
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->PhotosAlbum = new PhotosAlbum;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PhotosAlbum);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('MeCms\Model\Entity\PhotosAlbum', $this->PhotosAlbum);
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
        $this->assertEquals(['path'], $this->PhotosAlbum->getVirtual());
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->assertNull($this->PhotosAlbum->path);

        $this->PhotosAlbum->id = 1;
        $this->assertEquals(PHOTOS . '1', $this->PhotosAlbum->path);
    }
}
