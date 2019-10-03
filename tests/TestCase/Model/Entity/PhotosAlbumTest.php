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
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Entity->set('id', 1)->set('slug', 'a-slug');
    }

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
        $this->assertHasVirtualField(['path', 'preview', 'url']);
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->assertNotEmpty($this->Entity->get('path'));
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $path = WWW_ROOT . 'img' . DS . 'photos' . DS . '1' . DS . 'photo.jpg';
        copy(WWW_ROOT . 'img' . DS . 'image.jpg', $path);
        $this->Entity->set('photos', [new Photo(['album_id' => 1, 'filename' => basename($path)])]);
        $this->assertEquals($this->Entity->get('preview'), $path);
        unlink($path);
    }

    /**
     * Test for `_getUrl()` method
     * @test
     */
    public function testUrl()
    {
        $this->assertStringEndsWith('/album/a-slug', $this->Entity->get('url'));
    }
}
