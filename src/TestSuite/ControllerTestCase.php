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
 * @since       2.25.4
 */
namespace MeCms\TestSuite;

use Cake\Cache\Cache;
use MeTools\Controller\Component\UploaderComponent;
use MeTools\TestSuite\IntegrationTestCase;
use MeTools\TestSuite\Traits\MockTrait;

/**
 * Abstract class for test controllers
 */
abstract class ControllerTestCase extends IntegrationTestCase
{
    use MockTrait;

    /**
     * Controller instance
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Controller;

    /**
     * Table instance
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Table;

    /**
     * Cache keys to clear for each test
     * @var array
     */
    protected $cacheToClear = [];

    /**
     * @var array
     */
    protected $url;

    /**
     * Asserts that the controller has a component
     * @param string $component Component name
     * @param string|null $action Optional action for this assert
     * @return void
     * @uses $Controller
     */
    public function assertHasComponent($component, $action = null)
    {
        $this->Controller ?: $this->fail('The property `$this->Controller` has not been set');

        $controller = &$this->Controller;

        if ($action) {
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
        }
        $this->Controller->initialize();

        $this->assertTrue($this->Controller->components()->has($component));

        $this->Controller = $controller;
    }

    /**
     * Asserts that groups are authorized
     * @param array $values Group name as key and boolean as value
     * @param string|null $action Optional action for this assert
     * @return void
     * @uses $Controller
     * @uses setUserGroup()
     */
    public function assertGroupsAreAuthorized($values, $action = null)
    {
        $this->Controller ?: $this->fail('The property `$this->Controller` has not been set');

        $controller = &$this->Controller;
        $controller->request->clearDetectorCache();

        if ($action) {
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
        }

        foreach ($values as $group => $isAllowed) {
            $this->setUserGroup($group);
            $this->assertEquals($isAllowed, $this->Controller->isAuthorized());
        }

        $this->Controller = $controller;
    }

    /**
     * Asserts that users are authorized
     * @param array $values UserID as key and boolean as value
     * @param string|null $action Optional action for this assert
     * @return void
     * @uses $Controller
     * @uses setUserId()
     */
    public function assertUsersAreAuthorized($values, $action = null)
    {
        $this->Controller ?: $this->fail('The property `$this->Controller` has not been set');

        $controller = &$this->Controller;
        $controller->request->clearDetectorCache();

        if ($action) {
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
        }

        foreach ($values as $id => $isAllowed) {
            $this->setUserId($id);
            $this->assertEquals($isAllowed, $this->Controller->isAuthorized());
        }

        $this->Controller = $controller;
    }

    /**
     * Called before every test method
     * @return void
     * @uses $Controller
     * @uses $Table
     * @uses $cacheToClear
     * @uses $url
     * @uses getControllerAlias()
     * @uses getMockForController()
     * @uses getMockForTable()
     * @uses setUserGroup()
     */
    public function setUp()
    {
        parent::setUp();

        $parts = explode('\\', get_class($this));
        $isAdminController = in_array('Admin', array_slice($parts, -2, 1));

        //Tries to retrieve controller and table from the class name
        if (!$this->Controller) {
            array_splice($parts, 1, 2, []);
            $parts[count($parts) - 1] = substr($parts[count($parts) - 1], 0, -4);
            $className = implode('\\', $parts);
            $alias = $this->getControllerAlias($className);

            $this->Controller = $this->getMockForController($className, null, $alias);

            $this->url = ['controller' => $alias, 'plugin' => $parts[0]];
            if ($isAdminController) {
                $this->url['prefix'] = ADMIN_PREFIX;
            }

            //Tries to retrieve the table
            $className = sprintf('%s\\Model\\Table\\%sTable', $parts[0], $alias);
            if (class_exists($className)) {
                $this->Table = $this->getMockForTable($className, null);

                //Tries to retrieve all cache keys related to the table and associated tables
                foreach (array_merge([$this->Table], iterator_to_array($this->Table->associations())) as $table) {
                    if (!empty($table->cache)) {
                        $this->cacheToClear[] = $table->cache;
                    }
                }
            }
        }

        //Clears all cache keys
        foreach ($this->cacheToClear as $cacheKey) {
            Cache::getConfig($cacheKey) ?: $this->fail('Cache key `' . $cacheKey . '` does not exist');
            Cache::clear(false, $cacheKey);
        }

        if ($isAdminController) {
            $this->setUserGroup('admin');
        }
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        safe_unlink_recursive(LOGS);

        parent::tearDown();
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     * @uses getMockForComponent()
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        $this->_controller->viewBuilder()->setLayout('with_flash');

        //Sets key for cookies
        if (!$this->_controller->components()->has('Cookie')) {
            $this->_controller->loadComponent('Cookie');
        }
        $this->_controller->Cookie->setConfig('key', 'somerandomhaskeysomerandomhaskey');

        if ($this->_controller->components()->has('Uploader')) {
            $this->_controller->Uploader = $this->getMockForComponent(UploaderComponent::class, ['move_uploaded_file']);

            $this->_controller->Uploader->method('move_uploaded_file')
                ->will($this->returnCallback(function ($filename, $destination) {
                    return rename($filename, $destination);
                }));
        }
    }

    /**
     * Internal method to create an image to upload.
     *
     * It returns an array, similar to the `$_FILE` array that is created after
     *  a upload
     * @return array
     */
    protected function createImageToUpload()
    {
        $file = TMP . 'file_to_upload.jpg';
        safe_copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);

        return [
            'tmp_name' => $file,
            'error' => UPLOAD_ERR_OK,
            'name' => basename($file),
            'type' => mime_content_type($file),
            'size' => filesize($file),
        ];
    }

    /**
     * Internal method to set the user ID
     * @param int $id User ID
     * @return void
     * @uses $Controller
     */
    protected function setUserId($id)
    {
        if (!empty($this->Controller)) {
            $this->Controller->Auth->setUser(compact('id'));
        }

        $this->session(['Auth.User.id' => $id]);
    }

    /**
     * Internal method to set the user group
     * @param string $name Group name
     * @return void
     * @uses $Controller
     */
    protected function setUserGroup($name)
    {
        if (!empty($this->Controller)) {
            $this->Controller->Auth->setUser(['group' => compact('name')]);
        }

        $this->session(['Auth.User.group.name' => $name]);
    }

    /**
     * Tests for `isAuthorized()` method.
     *
     * This is a default tests.
     * @return void
     * @test
     */
    public function testIsAuthorized()
    {
        $this->Controller ?: $this->fail('The property `$this->Controller` has not been set');

        if (!$this->Controller->request->getParam('prefix')) {
            $this->assertTrue($this->Controller->isAuthorized());
        } elseif ($this->Controller->request->isAdmin()) {
            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => true,
                'user' => false,
            ]);
        } else {
            $this->assertFalse($this->Controller->isAuthorized());
        }
    }
}
