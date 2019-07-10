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
use MeCms\TestSuite\EntityTestCase;

/**
 * PhotoTest class
 */
class PhotoTest extends EntityTestCase
{
    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        @unlink_recursive(PHOTOS, 'empty');
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
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
        $this->assertNull($this->Entity->path);

        $this->Entity->set(['album_id' => 1, 'filename' => 'photo.jpg']);
        $this->assertEquals(PHOTOS . $this->Entity->album_id . DS . $this->Entity->filename, $this->Entity->path);
    }

    /**
     * Test for `_getPlainDescription()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertNull($this->Entity->plain_description);

        $this->assertEquals('This is a text', $this->Entity->set('description', 'This is a [readmore /]text')->get('plain_description'));
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $this->assertNull($this->Entity->preview);

        $this->Entity->set(['album_id' => 1, 'filename' => 'photo1.jpg']);
        @copy(WWW_ROOT . 'img' . DS . 'image.jpg', $this->Entity->path);
        $this->assertInstanceof(Entity::class, $this->Entity->preview);
        $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+/', $this->Entity->preview->url);
        $this->assertEquals([400, 400], [$this->Entity->preview->width, $this->Entity->preview->height]);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField(['path', 'plain_description', 'preview']);
    }
}
