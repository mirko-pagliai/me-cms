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
 * @since       2.26.5
 */
namespace MeCms\TestSuite;

use Cake\Utility\Inflector;
use MeCms\TestSuite\ControllerTestCase;

/**
 * Abstract class for `Admin/BannersController` and `Admin/PhotosController` classes
 */
abstract class BannersAndPhotosAdminControllerTestCase extends ControllerTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->associatedTable = array_value_first(iterator_to_array($this->Table->associations()))->getAlias();
        $this->foreignKey = $this->Table->{$this->associatedTable}->getForeignKey();
        $this->parentController = $this->Controller->getName() . $this->associatedTable;
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

        $alias = $this->Table->getRegistryAlias();
        //Only for the `testUploadErrorOnSave()` method, it mocks the table, so
        //  the `save()` method returns `false`
        if ($this->getName() == 'testUploadErrorOnSave') {
            $this->_controller->$alias = $this->getMockForModel('MeCms.' . $this->Table->getRegistryAlias(), ['save']);
            $this->_controller->$alias->method('save')->will($this->returnValue(false));
        }
    }

    /**
     * Tests for `beforeFilter()` method
     * @return void
     * @test
     */
    public function testBeforeFilter()
    {
        $this->Table->{$this->associatedTable}->deleteAll(['id IS NOT' => null]);
        $this->get($this->url + ['action' => 'index']);
        $this->assertRedirect(['controller' => $this->parentController, 'action' => 'index']);
        $this->assertFlashMessage('You must first create a banner position');
    }

    /**
     * Tests for `index()` method
     * @return void
     * @test
     */
    public function testIndex()
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'index.ctp');
        $this->assertContainsOnlyInstancesOf(
            $this->Table->getEntityClass(),
            $this->viewVariable(strtolower($this->Controller->getName()))
        );
        $this->assertCookieIsEmpty('render-' . strtolower($this->Controller->getName()));
    }

    /**
     * Tests for `index()` method, render as `grid`
     * @return void
     * @test
     */
    public function testIndexAsGrid()
    {
        $this->get($this->url + ['action' => 'index', '?' => ['render' => 'grid']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'index_as_grid.ctp');
        $this->assertContainsOnlyInstancesOf(
            $this->Table->getEntityClass(),
            $this->viewVariable(strtolower($this->Controller->getName()))
        );
        $this->assertCookie('grid', 'render-' . strtolower($this->Controller->getName()));

        //With cookie
        $this->cookie('render-banners', 'grid');
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'index_as_grid.ctp');
        $this->assertCookie('grid', 'render-' . strtolower($this->Controller->getName()));
    }

    /**
     * Tests for `upload()` method
     * @return void
     * @test
     */
    public function testUpload()
    {
        $url = $this->url + ['action' => 'upload'];

        //GET request
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'upload.ctp');

        //POST request. This works
        $file = $this->createImageToUpload();
        $this->post($url + ['_ext' => 'json', '?' => [substr($this->foreignKey, 0, -3) => 1]], compact('file'));
        $this->assertResponseOkAndNotEmpty();

        //Checks the record has been saved
        $record = $this->Table->find()->last();
        $this->assertEquals(1, $record->get($this->foreignKey));
        $this->assertEquals($file['name'], $record->get('filename'));
        $this->assertFileExists($record->get('path'));
    }

    /**
     * Tests for `upload()` method, error during the upload
     * @return void
     * @test
     */
    public function testUploadErrorDuringUpload()
    {
        $file = ['error' => UPLOAD_ERR_NO_FILE] + $this->createImageToUpload();

        $this->post($this->url + ['action' => 'upload', '_ext' => 'json', '?' => [substr($this->foreignKey, 0, -3) => 1]], compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"No file was uploaded"}');
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'json' . DS . 'upload.ctp');
    }

    /**
     * Tests for `upload()` method, error on entity
     * @return void
     * @test
     */
    public function testUploadErrorOnEntity()
    {
        $file = ['name' => 'a.pdf'] + $this->createImageToUpload();

        $this->post($this->url + ['action' => 'upload', '_ext' => 'json', '?' => [substr($this->foreignKey, 0, -3) => 1]], compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"Valid extensions: gif, jpg, jpeg, png"}');
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'json' . DS . 'upload.ctp');
    }

    /**
     * Tests for `upload()` method, error on save
     * @return void
     * @test
     */
    public function testUploadErrorOnSave()
    {
        $file = $this->createImageToUpload();

        //The table `save()` method returns `false` for this test.
        //See the `controllerSpy()` method.
        $this->post($this->url + ['action' => 'upload', '_ext' => 'json', '?' => [substr($this->foreignKey, 0, -3) => 1]], compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"' . I18N_OPERATION_NOT_OK . '"}');
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'json' . DS . 'upload.ctp');
    }

    /**
     * Tests for `upload()` method, error, missing ID on the query string
     * @return void
     * @test
     */
    public function testUploadErrorMissingPositionIdOnQueryString()
    {
        $this->post($this->url + ['action' => 'upload', '_ext' => 'json'], ['file' => true]);
        $this->assertResponseFailure();
        $this->assertResponseContains('Missing ID');
    }

    /**
     * Tests for `upload()` method, with only one position
     * @return void
     * @test
     */
    public function testUploadOnlyOnePosition()
    {
        //Deletes all record from the associated table, except for the first one
        $this->Table->{$this->associatedTable}->deleteAll(['id >' => 1]);

        //POST request. This should also work without the parent ID on the
        //  query string, as there is only one record from the associated table
        $file = $this->createImageToUpload();
        $this->post($this->url + ['action' => 'upload', '_ext' => 'json'], compact('file'));
        $this->assertResponseOkAndNotEmpty();

        //Checks the record has been saved
        $record = $this->Table->find()->last();
        $this->assertEquals(1, $record->get($this->foreignKey));
        $this->assertEquals($file['name'], $record->get('filename'));
        $this->assertFileExists($record->get('path'));
    }

    /**
     * Tests for `edit()` method
     * @return void
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 1];
        $viewVariableName = Inflector::singularize(strtolower($this->Controller->getName()));

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . $this->Controller->getName() . DS . 'edit.ctp');
        $this->assertInstanceof($this->Table->getEntityClass(), $this->viewVariable($viewVariableName));

        //POST request. Data are valid
        $this->post($url, ['description' => 'New description for first record']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['target' => 'invalidTarget']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof($this->Table->getEntityClass(), $this->viewVariable($viewVariableName));
    }

    /**
     * Tests for `download()` method
     * @return void
     * @test
     */
    public function testDownload()
    {
        $record = $this->Table->get(1);
        $this->get($this->url + ['action' => 'download', 1]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertFileResponse($record->path);
    }

    /**
     * Tests for `delete()` method
     * @return void
     * @test
     */
    public function testDelete()
    {
        $record = $this->Table->get(1);
        $this->assertFileExists($record->path);
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(1)->isEmpty());
        $this->skipIf(IS_WIN);
        $this->assertFileNotExists($record->path);
    }
}
