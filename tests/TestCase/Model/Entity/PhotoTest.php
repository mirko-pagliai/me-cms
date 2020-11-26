<?php
declare(strict_types=1);

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

use Cake\ORM\Entity;
use MeCms\Model\Entity\PhotosAlbum;
use MeCms\TestSuite\EntityTestCase;

/**
 * PhotoTest class
 */
class PhotoTest extends EntityTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Entity->set('id', 1)->set([
            'album_id' => 1,
            'filename' => 'photo.jpg',
            'description' => 'This is a [readmore /]text',
            'album' => new PhotosAlbum(['slug' => 'album-slug']),
        ]);
    }

    /**
     * Test for fields that cannot be mass assigned
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'modified']);
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $expected = PHOTOS . $this->Entity->get('album_id') . DS . $this->Entity->get('filename');
        $this->assertEquals($expected, $this->Entity->get('path'));
    }

    /**
     * Test for `_getPlainDescription()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertEquals('This is a text', $this->Entity->get('plain_description'));
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        @copy(WWW_ROOT . 'img' . DS . 'image.jpg', $this->Entity->get('path'));
        $this->assertInstanceof(Entity::class, $this->Entity->get('preview'));
        $this->assertRegExp('/\/thumb\/[\w\d]+/', $this->Entity->get('preview')->get('url'));
        $this->assertSame(400, $this->Entity->get('preview')->get('width'));
        $this->assertSame(400, $this->Entity->get('preview')->get('height'));
        @unlink($this->Entity->get('path'));
    }

    /**
     * Test for `_getUrl()` method
     * @test
     */
    public function testUrl()
    {
        $this->assertStringEndsWith('/photo/album-slug/1', $this->Entity->get('url'));
    }
}
