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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;
use MeCms\Controller\Admin\PhotosController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PhotosControllerTest class
 */
class PhotosControllerTest extends IntegrationTestCase
{
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
    protected function createFileToUpload()
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

        $this->Photos = TableRegistry::get(ME_CMS . '.Photos');

        Cache::clear(false, $this->Photos->cache);

        $this->url = ['controller' => 'Photos', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        //Mocks the `Uploader` component
        $this->_controller->Uploader = $this->getMockBuilder(get_class($this->_controller->Uploader))
            ->setConstructorArgs([new ComponentRegistry])
            ->setMethods(['move_uploaded_file'])
            ->getMock();

        $this->_controller->Uploader->method('move_uploaded_file')
            ->will($this->returnCallback(function ($filename, $destination) {
                return rename($filename, $destination);
            }));

        //Only for the `testUploadErrorOnSave()` method, it mocks the `Photos`
        //  table, so the `save()` method returns `false`
        if ($this->getName() === 'testUploadErrorOnSave') {
            $this->_controller->Photos = $this->getMockForModel($this->_controller->Photos->getRegistryAlias(), ['save']);
            $this->_controller->Photos->method('save')->will($this->returnValue(false));
        }
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
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
        $this->assertFlashMessage('You must first create an album');
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/index.ctp');

        $photosFromView = $this->viewVariable('photos');
        $this->assertNotEmpty($photosFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photosFromView);
        $this->assertCookieIsEmpty('renderPhotos');
    }

    /**
     * Tests for `index()` method, render as `grid`
     * @test
     */
    public function testIndexAsGrid()
    {
        $this->get(array_merge($this->url, ['action' => 'index', '?' => ['render' => 'grid']]));
        $this->assertResponseOkAndNotEmpty();
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/index_as_grid.ctp');
        $this->assertCookie('grid', 'renderPhotos');
    }

    /**
     * Tests for `upload()` method
     * @test
     */
    public function testUpload()
    {
        $file = $this->createFileToUpload();
        $url = array_merge($this->url, ['action' => 'upload']);

        //GET request
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/upload.ctp');

        //POST request. This works
        $this->post(array_merge($url, ['_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseOkAndNotEmpty();

        //Checks the photo has been saved
        $photo = $this->Photos->find()->last();
        $this->assertEquals(1, $photo['album_id']);
        $this->assertTextContains('file_to_upload', $photo['filename']);
    }

    /**
     * Tests for `upload()` method, error during the upload
     * @test
     */
    public function testUploadErrorDuringUpload()
    {
        $file = array_merge($this->createFileToUpload(), ['error' => UPLOAD_ERR_NO_FILE]);

        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"No file was uploaded"}');
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/json/upload.ctp');
    }

    /**
     * Tests for `upload()` method, error, missing ID on the query string
     * @test
     */
    public function testUploadErrorMissingAlbumIdOnQueryString()
    {
        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json']), ['file' => true]);
        $this->assertResponseFailure();
        $this->assertResponseContains('Missing ID');
    }

    /**
     * Tests for `upload()` method, error on save
     * @test
     */
    public function testUploadErrorOnSave()
    {
        $file = $this->createFileToUpload();

        //The table `save()` method returns `false` for this test. See the
        //  `controllerSpy()` method.
        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"' . I18N_OPERATION_NOT_OK . '"}');
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/json/upload.ctp');
    }

    /**
     * Tests for `upload()` method, error on entity
     * @test
     */
    public function testUploadErrorOnEntity()
    {
        $file = array_merge($this->createFileToUpload(), ['name' => 'a.jpg?name=value']);

        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json', '?' => ['album' => 1]]), compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"Valid extensions: gif, jpg, jpeg, png"}');
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/json/upload.ctp');
    }

    /**
     * Tests for `upload()` method, with only one album
     * @test
     */
    public function testUploadOnlyOneAlbum()
    {
        $file = $this->createFileToUpload();

        //Deletes all albums, except for the first one
        $this->Photos->Albums->deleteAll(['id >' => 1]);

        //POST request. This should also work without the album ID on the query
        //  string, as there is only one album
        $this->post(array_merge($this->url, ['action' => 'upload', '_ext' => 'json']), compact('file'));
        $this->assertResponseOkAndNotEmpty();

        //Checks the photo has been saved
        $photo = $this->Photos->find()->last();
        $this->assertEquals(1, $photo['album_id']);
        $this->assertTextContains('file_to_upload', $photo['filename']);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Photos/edit.ctp');

        $photoFromView = $this->viewVariable('photo');
        $this->assertNotEmpty($photoFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photoFromView);

        //POST request. Data are valid
        $this->post($url, ['description' => 'New description for first banner']);
        $this->assertRedirect(['action' => 'index', 1]);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['album_id' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $photoFromView = $this->viewVariable('photo');
        $this->assertNotEmpty($photoFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Photo', $photoFromView);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        $this->get(array_merge($this->url, ['action' => 'download', 1]));
        $this->assertResponseOkAndNotEmpty();
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
        $this->assertFlashMessage('The operation has been performed correctly');
    }
}
