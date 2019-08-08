<?php
declare(strict_types=1);
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

use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * Abstract class for test controllers
 */
abstract class ControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Controller instance
     * @var \Cake\Controller\Controller|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Controller;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * @var array
     */
    protected $url;

    /**
     * Asserts that groups are authorized
     * @param array $values Group name as key and boolean as value
     * @param string|null $action Optional action for this assert
     * @return void
     * @uses $Controller
     * @uses setUserGroup()
     */
    public function assertGroupsAreAuthorized(array $values, ?string $action = null): void
    {
        $this->Controller ?: $this->fail('The property `$this->Controller` has not been set');

        $controller = &$this->Controller;
        $this->Controller->getRequest()->clearDetectorCache();

        if ($action) {
            $this->Controller->request = $this->Controller->getRequest()->withParam('action', $action);
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
    public function assertUsersAreAuthorized(array $values, ?string $action = null): void
    {
        $this->Controller ?: $this->fail('The property `$this->Controller` has not been set');

        $controller = &$this->Controller;
        $controller->getRequest()->clearDetectorCache();

        if ($action) {
            $this->Controller->request = $this->Controller->getRequest()->withParam('action', $action);
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
     * @uses $autoInitializeClass
     * @uses $cacheToClear
     * @uses $url
     * @uses getControllerAlias()
     * @uses getMockForController()
     * @uses getMockForModel()
     * @uses setUserGroup()
     */
    public function setUp(): void
    {
        parent::setUp();

        $parts = explode('\\', get_class($this));
        $isAdminController = $parts[count($parts) - 2] === 'Admin';

        //Tries to retrieve controller and table from the class name
        if (!$this->Controller && $this->autoInitializeClass) {
            array_splice($parts, 1, 2, []);
            $parts[count($parts) - 1] = substr($parts[count($parts) - 1], 0, -4);
            $className = implode('\\', $parts);
            $alias = $this->getControllerAlias($className);

            $this->Controller = $this->getMockForController($className, null, $alias);
            $this->url = ['controller' => $alias, 'plugin' => $parts[0]];
            $this->url += $isAdminController ? ['prefix' => ADMIN_PREFIX] : [];

            $className = $parts[0] . '\\Model\\Table\\' . $alias . 'Table';
            $this->Table = $this->getTable($alias, compact('className'));
        }

        if ($isAdminController) {
            $this->setUserGroup('admin');
        }
    }

    /**
     * Internal method to create an image to upload.
     *
     * It returns an array, similar to the `$_FILE` array that is created after
     *  a upload
     * @return array
     */
    protected function createImageToUpload(): array
    {
        $file = TMP . 'file_to_upload.jpg';
        @copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);

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
    protected function setUserId(int $id): void
    {
        if ($this->Controller) {
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
    protected function setUserGroup(string $name): void
    {
        if ($this->Controller) {
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
        $this->assertTrue($this->Controller->isAuthorized());
    }
}
