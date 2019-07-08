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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use MeCms\Model\Entity\Photo;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PhotosControllerTest class
 */
class PhotosControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Photos',
        'plugin.MeCms.PhotosAlbums',
    ];

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $url = ['_name' => 'photo', 'test-album', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Photos' . DS . 'view.ctp');
        $this->assertInstanceof(Photo::class, $this->viewVariable('photo'));
        $cache = Cache::read('view_' . md5('1'), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('photo'), $cache->first());

        //Backward compatibility for URLs like `/photo/1`
        $this->get('/photo/1');
        $this->assertRedirect($url);
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $this->get(['_name' => 'photosPreview', 4]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Photos' . DS . 'view.ctp');
        $this->assertInstanceof(Photo::class, $this->viewVariable('photo'));
    }
}
