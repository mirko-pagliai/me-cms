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
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MeCms\Model\Entity\Photo;

/**
 * PhotoTest class
 */
class PhotoTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\Photo
     */
    protected $Photo;

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

        $this->Photo = new Photo;

        $this->Photos = TableRegistry::get('MeCms.Photos');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Photo, $this->Photos);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('MeCms\Model\Entity\Photo', $this->Photo);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->Photo->isAccessible('id'));
        $this->assertFalse($this->Photo->isAccessible('modified'));
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->assertNull($this->Photo->path);

        $this->Photo->album_id = 1;
        $this->assertNull($this->Photo->path);

        $this->Photo->filename = 'photo.jpg';
        $this->assertEquals(PHOTOS . '1' . DS . 'photo.jpg', $this->Photo->path);

        unset($this->Photo->album_id);
        $this->assertNull($this->Photo->path);
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $photo = $this->Photos->get(1);

        $this->assertEquals($photo->thumbnail, $photo->preview['preview']);
        $this->assertEquals([
            'preview' => 'http://localhost/thumb/ZWQyMTVlM2QwM2UxMTFmNjQ5NzE3ZWNkNWUyZmIwODkuanBn',
            'width' => 400,
            'height' => 400,
        ], $photo->preview);
    }

    /**
     * Test for `_getThumbnail()` method
     * @test
     */
    public function testThumbnailGetMutator()
    {
        $thumbnail = $this->Photos->get(1)->thumbnail;
        $this->assertEquals('http://localhost/thumb/ZWQyMTVlM2QwM2UxMTFmNjQ5NzE3ZWNkNWUyZmIwODkuanBn', $thumbnail);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['path', 'preview', 'thumbnail'], $this->Photo->getVirtual());
    }
}
