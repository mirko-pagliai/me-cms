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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\PhotosController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;
use MeTools\Controller\Component\UploaderComponent;

/**
 * PhotosControllerTest class
 */
class PhotosControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\PhotosController
     */
    protected $Controller;

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
        'plugin.me_cms.photos_albums',
    ];

    /**
     * @var array
     */
    protected $url;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUserGroup('admin');

        $this->Controller = new PhotosController;

        $this->Photos = TableRegistry::get('MeCms.Photos');

        Cache::clear(false, $this->Photos->cache);

        $this->url = ['controller' => 'Photos', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->Photos);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        //Sets key for cookies
        $controller->Cookie->config('key', 'somerandomhaskeysomerandomhaskey');

        //Mocks the `Uploader` component
        $controller->Uploader = $this->getMockBuilder(UploaderComponent::class)
            ->setConstructorArgs([new ComponentRegistry])
            ->getMock();

        $controller->Uploader->method('set')
            ->will($this->returnSelf());

        $controller->Uploader->method('mimetype')
            ->will($this->returnSelf());

        $controller->Uploader->method('save')
            ->will($this->returnValue('/full/path/to/file_to_upload.jpg'));

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertNotEmpty($this->viewVariable('albums'));
    }

    /**
     * Tests for `beforeFilter()` method, with no positions
     * @test
     */
    public function testBeforeFilterNoAlbums()
    {
        //Deletes all albums
        $this->Photos->Albums->deleteAll(['id IS NOT' => null]);

        $this->get(array_merge($this->url, ['action' => 'add']));
        $this->assertRedirect(['controller' => 'PhotosAlbums', 'action' => 'index']);
        $this->assertSession('You must first create an album', 'Flash.flash.0.message');
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => true,
        ]);

        //`delete` action
        $this->Controller = new PhotosController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'delete');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/index.ctp');

        $photosFromView = $this->viewVariable('photos');
        $this->assertInstanceof('Cake\ORM\ResultSet', $photosFromView);
        $this->assertNotEmpty($photosFromView);

        foreach ($photosFromView as $photo) {
            $this->assertInstanceof('MeCms\Model\Entity\Photo', $photo);
        }

        $this->assertCookie(null, 'renderPhotos');
    }

    /**
     * Tests for `index()` method, render as `grid`
     * @test
     */
    public function testIndexAsGrid()
    {
        $this->get(array_merge($this->url, ['action' => 'index', '?' => ['render' => 'grid']]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/index_as_grid.ctp');

        $this->assertCookie('grid', 'renderPhotos');
    }

    /**
     * Tests for `index()` method, render as `grid` with cookie
     * @test
     */
    public function testIndexWithCookie()
    {
        $this->cookie('renderPhotos', 'grid');

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/index_as_grid.ctp');

        $this->assertCookie('grid', 'renderPhotos');
    }

    /**
     * Tests for `upload()` method
     * @test
     */
    public function testUpload()
    {
        $url = array_merge($this->url, ['action' => 'upload']);

        $file = WWW_ROOT . 'img' . DS . 'photos' . DS . '1' . DS . 'file_to_upload.jpg';
        copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);

        //GET request
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/upload.ctp');

        //POST request. Tries to upload a photo
        $this->post(array_merge($url, ['?' => ['album' => 1]]), ['file' => true]);
        $this->assertResponseOk();

        //Checks the photo has been saved
        $photo = $this->Photos->find()->last();
        $this->assertEquals(1, $photo['album_id']);
        $this->assertEquals('file_to_upload.jpg', $photo['filename']);

        //Deletes all albums (except for the first one) and all photos
        $this->Photos->deleteAll(['id >=' => 1]);
        $this->Photos->Albums->deleteAll(['id >' => 1]);

        //POST request again. Tries to upload a photo
        //This should also work without the album in the query string, as
        //  there is only one album
        $this->post($url, ['file' => true]);
        $this->assertResponseOk();

        //Checks the photo has been saved
        $photo = $this->Photos->find()->last();
        $this->assertEquals(1, $photo['album_id']);
        $this->assertEquals('file_to_upload.jpg', $photo['filename']);

        //@codingStandardsIgnoreLine
        @unlink($file);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/edit.ctp');

        $photoFromView = $this->viewVariable('photo');
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photoFromView);
        $this->assertNotEmpty($photoFromView);

        //POST request. Data are valid
        $this->post($url, ['description' => 'New description for first banner']);
        $this->assertRedirect(['action' => 'index', 1]);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['album_id' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $photoFromView = $this->viewVariable('photo');
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photoFromView);
        $this->assertNotEmpty($photoFromView);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        $this->get(array_merge($this->url, ['action' => 'download', 1]));
        $this->assertResponseOk();
        $this->assertFileResponse(PHOTOS . '1' . DS . 'photo1.jpg');
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->post(array_merge($this->url, ['action' => 'delete', 1]));
        $this->assertRedirect(['action' => 'index', 1]);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');
    }
}
