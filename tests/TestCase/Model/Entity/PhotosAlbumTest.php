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

use MeCms\Model\Entity\Photo;
use MeCms\TestSuite\EntityTestCase;

/**
 * PhotosAlbumTest class
 */
class PhotosAlbumTest extends EntityTestCase
{
    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'photo_count', 'modified']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField(['path', 'preview']);
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->Entity->id = 1;
        $this->assertNotEmpty($this->Entity->path);
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $this->Entity->id = 1;
        $this->assertNull($this->Entity->preview);

        $this->Entity->photos = [new Photo(['album_id' => 1, 'filename' => 'photo.jpg'])];
        $this->assertNotEmpty($this->Entity->photos[0]->path);
        $this->assertEquals($this->Entity->preview, $this->Entity->photos[0]->path);
    }
}
