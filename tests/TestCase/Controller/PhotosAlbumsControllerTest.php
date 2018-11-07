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
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PhotosAlbumsControllerTest class
 */
class PhotosAlbumsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosAlbumsTable
     */
    protected $PhotosAlbums;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Photos',
        'plugin.me_cms.PhotosAlbums',
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

        $this->PhotosAlbums = TableRegistry::get(ME_CMS . '.PhotosAlbums');

        Cache::clear(false, $this->PhotosAlbums->cache);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'albums']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PhotosAlbums/index.ctp');

        $albumsFromView = $this->viewVariable('albums');
        $this->assertNotEmpty($albumsFromView);
        $this->assertContainsInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumsFromView);
        $this->assertContainsInstanceof('MeCms\Model\Entity\Photo', $albumsFromView->first()->photos);

        $cache = Cache::read('albums_index', $this->PhotosAlbums->cache);
        $this->assertEquals($albumsFromView->toArray(), $cache->toArray());

        //Deletes all albums, except the first one and clears the cache
        $this->PhotosAlbums->deleteAll(['id !=' => 1]);
        Cache::clear(false, $this->PhotosAlbums->cache);

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
        $slug = $this->PhotosAlbums->find('active')->extract('slug')->first();
        $url = ['_name' => 'album', $slug];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PhotosAlbums/view.ctp');

        $albumFromView = $this->viewVariable('album');
        $this->assertNotEmpty($albumFromView);
        $this->assertInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumFromView);

        $cache = Cache::read(sprintf('album_%s', md5($slug)), $this->PhotosAlbums->cache);
        $this->assertEquals($albumFromView, $cache->first());

        $photosFromView = $this->viewVariable('photos');
        $this->assertNotEmpty($photosFromView);
        $this->assertContainsInstanceof('MeCms\Model\Entity\Photo', $photosFromView);

        //Sets the cache name
        $cache = sprintf('album_%s_limit_%s_page_%s', md5($slug), getConfigOrFail('default.photos'), 1);
        list($photosFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PhotosAlbums->cache
        ));

        $this->assertEquals($photosFromView->toArray(), $photosFromCache->toArray());
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
