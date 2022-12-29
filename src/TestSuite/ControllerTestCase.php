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
use Cake\Http\ServerRequest;
use MeCms\Controller\AppController;
use MeCms\Model\Table\AppTable;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * Abstract class for test controllers
 * @property \MeCms\Controller\AppController $_controller
 * @property \Cake\Http\Response $_response
 */
abstract class ControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var \MeCms\Controller\AppController|(\MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject)
     */
    protected AppController $Controller;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected bool $autoInitializeClass = true;

    /**
     * @var array<string, string>
     */
    protected array $url;

    /**
     * Asserts that groups are authorized
     * @param array $values Group name as key and boolean as value
     * @param string|null $action Optional action for this assert
     * @return void
     */
    public function assertGroupsAreAuthorized(array $values, ?string $action = null): void
    {
        !empty($this->Controller) ?: $this->fail('The property `$this->Controller` has not been set');

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
     */
    public function assertUsersAreAuthorized(array $values, ?string $action = null): void
    {
        !empty($this->Controller) ?: $this->fail('The property `$this->Controller` has not been set');

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
     * @throws \ReflectionException
     * @noinspection PhpRedundantVariableDocTypeInspection
     */
    protected function setUp(): void
    {
        parent::setUp();

        $isAdmin = str_contains(get_class($this), 'Controller\\Admin\\');

        //Tries to retrieve controller and table from the class name
        if (empty($this->Controller) && $this->autoInitializeClass) {
            /** @var class-string<\MeCms\Controller\AppController> $originClassName */
            $originClassName = $this->getOriginClassNameOrFail($this);
            $alias = $this->getAlias($originClassName);
            $plugin = $this->getPluginName($this);

            $this->url = ['controller' => $alias, 'prefix' => $isAdmin ? ADMIN_PREFIX : null] + compact('plugin');
            $Request = new ServerRequest(['params' => $this->url]);

            if (!(new \ReflectionClass($originClassName))->isAbstract()) {
                $Controller = new $originClassName($Request, null, $alias);
            } else {
                $Controller = $this->getMockForAbstractClass($originClassName, [$Request, null, $alias]);
            }
            $this->Controller = $Controller;

            if (empty($this->Table)) {
                $Table = false;
                try {
                    $Table = $this->getTable($plugin . '.' . $alias);
                } catch (\Error|\TypeError $e) {
                }
                if ($Table instanceof AppTable) {
                    $this->Table = $Table;
                }
            }
        }

        if ($isAdmin) {
            $this->setUserGroup('admin');
        }
    }

    /**
     * Internal method to create an image to upload.
     *
     * Returns an array, similar to the `$_FILE` array that is created after an upload
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
     */
    protected function setUserId(int $id): void
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
     */
    protected function setUserGroup(string $name): void
    {
        if (!empty($this->Controller)) {
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
     * @throws \ReflectionException
     */
    public function testBeforeFilter(): void
    {
        $originClassName = $this->getOriginClassNameOrFail($this);

        //If the user has been reported as a spammer this makes a redirect
        /** @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
        $Controller = $this->getMockBuilder($originClassName)
            ->setConstructorArgs([null, null, $this->getAlias($originClassName)])
            ->onlyMethods(['isSpammer'])
            ->getMock();
        $Controller->method('isSpammer')->willReturn(true);
        /** @var \Cake\Http\Response $response */
        $response = $Controller->beforeFilter(new Event('myEvent'));
        $this->_response = $response;
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
