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

use Cake\Event\Event;
use Cake\Http\Response;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * Abstract class for test controllers
 * @method \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject getMockForController(string $className, ?array $methods = [], ?string $alias = null)
 * @property \MeCms\Controller\AppController $_controller
 * @property \Cake\Http\Response $_response
 */
abstract class ControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Controller instance
     * @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject
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
            $this->Controller->setRequest($this->Controller->getRequest()->withParam('action', $action));
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
            $this->Controller->setRequest($this->Controller->getRequest()->withParam('action', $action));
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
     * @uses getControllerAlias()
     * @uses getMockForController()
     * @uses getOriginClassNameOrFail()
     * @uses getPluginName()
     * @uses setUserGroup()
     */
    public function setUp(): void
    {
        parent::setUp();

        $isAdmin = string_contains(get_class($this), 'Controller\\Admin');

        //Tries to retrieve controller and table from the class name
        if (!$this->Controller && $this->autoInitializeClass) {
            /** @var class-string<\MeCms\Controller\AppController> $originClassName */
            $originClassName = $this->getOriginClassNameOrFail($this);
            $alias = $this->getAlias($originClassName);
            $plugin = $this->getPluginName($this);

            $this->Controller = $this->getMockForController($originClassName, null, $alias);
            $this->url = ['controller' => $alias, 'prefix' => $isAdmin ? ADMIN_PREFIX : null] + compact('plugin');

            $className = $this->getTableClassNameFromAlias($alias, $plugin);
            if (class_exists($className)) {
                $this->Table = $this->getTable($alias, compact('className'));
            }
        }

        if ($isAdmin) {
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
        copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);

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
     * Tests for `beforeFilter()` method.
     *
     * This is a default tests.
     * @return void
     * @test
     */
    public function testBeforeFilter(): void
    {
        //If the user has been reported as a spammer this makes a redirect
        $controller = $this->getMockForController($this->getOriginClassName($this), ['isSpammer']);
        $controller->method('isSpammer')->willReturn(true);
        $this->_response = $controller->beforeFilter(new Event('myEvent')) ?: new Response();
        $this->assertRedirect(['_name' => 'ipNotAllowed']);
    }

    /**
     * Tests for `isAuthorized()` method.
     *
     * This is a default tests.
     * @return void
     * @test
     */
    public function testIsAuthorized(): void
    {
        $this->assertTrue($this->Controller->isAuthorized());
    }
}
