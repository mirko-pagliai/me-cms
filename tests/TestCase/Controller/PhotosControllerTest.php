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
        'plugin.me_cms.Photos',
        'plugin.me_cms.PhotosAlbums',
    ];

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $photo = $this->Table->find('active')->contain('Albums')->first();
        $url = ['_name' => 'photo', $photo->album->slug, $photo->id];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Photos/view.ctp');
        $this->assertInstanceof(Photo::class, $this->viewVariable('photo'));

        $cache = Cache::read(sprintf('view_%s', md5($photo->id)), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('photo'), $cache->first());

        //Backward compatibility for URLs like `/photo/11`
        $this->get('/photo/' . $photo->id);
        $this->assertRedirect($url);
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $id = $this->Table->find('pending')->extract('id')->first();

        $this->get(['_name' => 'photosPreview', $id]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Photos/view.ctp');
        $this->assertInstanceof(Photo::class, $this->viewVariable('photo'));
    }
}
