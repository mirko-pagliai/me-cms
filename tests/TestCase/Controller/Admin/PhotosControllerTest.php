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
     * Internal method to create a file to upload.
     *
     * It returns an array, similar to the `$_FILE` array that is created after
     *  a upload
     * @return array
     */
    protected function _createFileToUpload()
    {
        $file = TMP . 'file_to_upload.jpg';

        if (!file_exists($file)) {
            copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);
        }

        return [
            'tmp_name' => $file,
            'error' => UPLOAD_ERR_OK,
            'name' => basename($file),
            'type' => mime_content_type($file),
            'size' => filesize($file),
        ];
    }

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
            ->setMethods(['move_uploaded_file'])
            ->getMock();

        $controller->Uploader->method('move_uploaded_file')
            ->will($this->returnCallback(function ($filename, $destination) {
                return rename($filename, $destination);
            }));

        //Only for the `testUploadSaveFailure()` method, it mocks the `Photos`
        //  table, so the `save()` method returns `false`
        if ($this->getName() === 'testUploadSaveFailure') {
            $controller->Photos = $this->getMockBuilder(get_class($controller->Photos))
                ->setConstructorArgs([[
                    'table' =>  $controller->Photos->getTable(),
                    'connection' =>  $controller->Photos->getConnection(),
                ]])
                ->setMethods(['save'])
                ->getMock();

            $controller->Photos->method('save')->will($this->returnValue(false));
        }

        $controller->viewBuilder()->setLayout('with_flash');

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

        $file = $this->_createFileToUpload();

        //GET request
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/upload.ctp');

        //POST request. This works
        $this->post(array_merge($url, ['_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseOk();

        //Checks the photo has been saved
        $photo = $this->Photos->find()->last();
        $this->assertEquals(1, $photo['album_id']);
        $this->assertTextContains('file_to_upload', $photo['filename']);
    }

    /**
     * Tests for `upload()` method, simulating and error during the upload
     * @test
     */
    public function testUploadErrorDuringUpload()
    {
        $file = array_merge($this->_createFileToUpload(), ['error' => UPLOAD_ERR_NO_FILE]);

        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"No file was uploaded"}');
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/json/upload.ctp');
    }

    /**
     * Tests for `upload()` method, with only one album
     * @test
     */
    public function testUploadOnlyOneAlbum()
    {
        $file = $this->_createFileToUpload();

        //Deletes all albums, except for the first one
        $this->Photos->Albums->deleteAll(['id >' => 1]);

        //POST request. This should also work without the album ID on the query
        //  string, as there is only one album
        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json']), compact('file'));
        $this->assertResponseOk();

        //Checks the photo has been saved
        $photo = $this->Photos->find()->last();
        $this->assertEquals(1, $photo['album_id']);
        $this->assertTextContains('file_to_upload', $photo['filename']);
    }

    /**
     * Tests for `upload()` method, with a failure on save
     * @test
     */
    public function testUploadSaveFailure()
    {
        $file = $this->_createFileToUpload();

        //The table `save()` method returns `false` for this test. See the
        //  `controllerSpy()` method.
        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"The photo could not be saved"}');
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/json/upload.ctp');
    }

    /**
     * Tests for `upload()` method, without the album ID on the query string
     * @test
     */
    public function testUploadWithoutAlbumIdOnQueryString()
    {
        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json']), ['file' => true]);
        $this->assertResponseFailure();
        $this->assertResponseContains('Missing album ID');
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
