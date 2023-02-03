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
 */

namespace MeCms\Test\TestCase\Controller\Admin;

use MeCms\Model\Entity\UsersGroup;
use MeCms\TestSuite\Admin\ControllerTestCase;

/**
 * UsersGroupsControllerTest class
 * @group admin-controller
 */
class UsersGroupsControllerTest extends ControllerTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Tests for `isAuthorized()` method
     * @uses \MeCms\Controller\Admin\UsersGroupsController::isAuthorized()
     * @test
     */
    public function testIsAuthorized(): void
    {
        $this->assertOnlyAdminIsAuthorized('index');
    }

    /**
     * Tests for `index()` method
     * @uses \MeCms\Controller\Admin\UsersGroupsController::index()
     * @test
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'UsersGroups' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(UsersGroup::class, $this->viewVariable('groups'));
    }

    /**
     * Tests for `add()` method
     * @uses \MeCms\Controller\Admin\UsersGroupsController::add()
     * @test
     */
    public function testAdd(): void
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'UsersGroups' . DS . 'add.php');

        //POST request. Data are valid
        $this->post($url, ['name' => 'team', 'label' => 'Team']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['name' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(UsersGroup::class, $this->viewVariable('group'));
    }

    /**
     * Tests for `edit()` method
     * @uses \MeCms\Controller\Admin\UsersGroupsController::edit()
     * @test
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 2];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'UsersGroups' . DS . 'edit.php');
        $this->assertInstanceOf(UsersGroup::class, $this->viewVariable('group'));

        //POST request. Data are valid
        $this->post($url, ['description' => 'This is a description']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['label' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(UsersGroup::class, $this->viewVariable('group'));
    }

    /**
     * Tests for `delete()` method
     * @uses \MeCms\Controller\Admin\UsersGroupsController::delete()
     * @test
     */
    public function testDelete(): void
    {
        $url = $this->url + ['action' => 'delete'];

        $this->post($url + [5]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(5)->all()->isEmpty());

        //Cannot delete a default group
        $this->post($url + [2]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('You cannot delete this users group');
        $this->assertFalse($this->Table->findById(2)->all()->isEmpty());

        //Cannot delete a group with users
        $this->post($url + [4]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
        $this->assertFalse($this->Table->findById(4)->all()->isEmpty());
    }
}
