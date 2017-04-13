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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

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
        'plugin.me_cms.photos',
        'plugin.me_cms.photos_albums',
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

        $this->PhotosAlbums = TableRegistry::get('MeCms.PhotosAlbums');

        Cache::clear(false, $this->PhotosAlbums->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PhotosAlbums);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'albums']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PhotosAlbums/index.ctp');

        $albumsFromView = $this->viewVariable('albums');
        $this->assertInstanceof('Cake\ORM\ResultSet', $albumsFromView);

        foreach ($albumsFromView as $album) {
            $this->assertInstanceOf('MeCms\Model\Entity\PhotosAlbum', $album);

            $this->assertGreaterThan(0, count($album->photos));
            $this->assertInstanceOf('MeCms\Model\Entity\Photo', collection($album->photos)->first());
        }

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
        $slug = 'test-album';
        $url = ['_name' => 'album', $slug];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PhotosAlbums/view.ctp');

        $albumFromView = $this->viewVariable('album');
        $this->assertInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumFromView);

        $cache = Cache::read(sprintf('album_%s', md5($slug)), $this->PhotosAlbums->cache);
        $this->assertEquals($albumFromView, $cache->first());

        $photosFromView = $this->viewVariable('photos');
        $this->assertTrue(is_array($photosFromView));

        foreach ($photosFromView as $photo) {
            $this->assertInstanceof('MeCms\Model\Entity\Photo', $photo);
        }

        //Sets the cache name
        $cache = sprintf('album_%s_limit_%s_page_%s', md5($slug), config('default.photos'), 1);
        list($photosFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PhotosAlbums->cache
        ));

        $this->assertEquals($photosFromView, $photosFromCache);
        $this->assertNotEmpty($pagingFromCache);

        $this->get(array_merge($url, ['?' => ['q' => $slug]]));
        $this->assertRedirect($url);
    }
}
