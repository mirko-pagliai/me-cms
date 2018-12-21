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
use MeCms\Model\Entity\PhotosAlbum;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PhotosAlbumsControllerTest class
 */
class PhotosAlbumsControllerTest extends ControllerTestCase
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
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'albums']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PhotosAlbums/index.ctp');
        $this->assertContainsInstanceof(PhotosAlbum::class, $this->viewVariable('albums'));

        foreach ($this->viewVariable('albums') as $album) {
            $this->assertContainsInstanceof(Photo::class, $album->photos);
        }

        $cache = Cache::read('albums_index', $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('albums')->toArray(), $cache->toArray());

        //Deletes all albums, except the first one and clears the cache
        $this->Table->deleteAll(['id !=' => 1]);
        Cache::clear(false, $this->Table->getCacheName());

        //Now it redirects to the first album
        $this->get(['_name' => 'albums']);
        $this->assertRedirect(['_name' => 'album', 'test-album']);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->Table->find('active')->extract('slug')->first();
        $url = ['_name' => 'album', $slug];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PhotosAlbums/view.ctp');
        $this->assertInstanceof(PhotosAlbum::class, $this->viewVariable('album'));
        $this->assertContainsInstanceof(Photo::class, $this->viewVariable('photos'));

        $cache = Cache::read(sprintf('album_%s', md5($slug)), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('album'), $cache->first());

        //Sets the cache name
        $cache = sprintf('album_%s_limit_%s_page_%s', md5($slug), getConfigOrFail('default.photos'), 1);
        list($photosFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));

        $this->assertEquals($this->viewVariable('photos')->toArray(), $photosFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Photos']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Photos']);

        //GET request with query string
        $this->get($url + ['?' => ['q' => $slug]]);
        $this->assertRedirect($url);
    }
}
