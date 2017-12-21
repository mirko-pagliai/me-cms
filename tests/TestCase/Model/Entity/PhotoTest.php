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
use MeCms\Model\Entity\Photo;
use MeTools\TestSuite\TestCase;

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
        $this->Photos = TableRegistry::get(ME_CMS . '.Photos');

        Cache::clear(false, $this->Photos->cache);
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
        $this->Photo->album_id = 1;
        $this->Photo->filename = 'photo.jpg';
        $this->assertEquals(PHOTOS . $this->Photo->album_id . DS . $this->Photo->filename, $this->Photo->path);
    }

    /**
     * Test for `_getPlainDescription()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertEquals('A photo', $this->Photos->findById(1)->first()->plain_description);
        $this->assertEmpty((new Photo)->plain_description);
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $photo = $this->Photos->get(1);

        $this->assertEquals(['preview', 'width', 'height'], array_keys($photo->preview));
        $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+/', $photo->preview['preview']);
        $this->assertEquals(400, $photo->preview['width']);
        $this->assertEquals(400, $photo->preview['height']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['path', 'plain_description', 'preview'], $this->Photo->getVirtual());
    }
}
