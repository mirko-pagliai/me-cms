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
use Cake\ORM\TableRegistry;
use MeCms\Controller\Admin\UsersGroupsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * UsersGroupsControllerTest class
 */
class UsersGroupsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\Admin\UsersGroupsController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\UsersGroupsTable
     */
    protected $UsersGroups;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users_groups',
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

        $this->Controller = new UsersGroupsController;

        $this->UsersGroups = TableRegistry::get(ME_CMS . '.UsersGroups');

        Cache::clear(false, $this->UsersGroups->cache);

        $this->url = ['controller' => 'UsersGroups', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/UsersGroups/index.ctp');

        $groupsFromView = $this->viewVariable('groups');
        $this->assertNotEmpty($groupsFromView);
        $this->assertContainsInstanceof('MeCms\Model\Entity\UsersGroup', $groupsFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/UsersGroups/add.ctp');

        $groupFromView = $this->viewVariable('group');
        $this->assertNotEmpty($groupFromView);
        $this->assertInstanceof('MeCms\Model\Entity\UsersGroup', $groupFromView);

        //POST request. Data are valid
        $this->post($url, ['name' => 'team', 'label' => 'Team']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['name' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $groupFromView = $this->viewVariable('group');
        $this->assertNotEmpty($groupFromView);
        $this->assertInstanceof('MeCms\Model\Entity\UsersGroup', $groupFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 2];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/UsersGroups/edit.ctp');

        $groupFromView = $this->viewVariable('group');
        $this->assertNotEmpty($groupFromView);
        $this->assertInstanceof('MeCms\Model\Entity\UsersGroup', $groupFromView);

        //POST request. Data are valid
        $this->post($url, ['description' => 'This is a description']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['label' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $groupFromView = $this->viewVariable('group');
        $this->assertNotEmpty($groupFromView);
        $this->assertInstanceof('MeCms\Model\Entity\UsersGroup', $groupFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $url = $this->url + ['action' => 'delete'];

        $id = $this->UsersGroups->find()
            ->where(['id <=' => 3, 'user_count' => 0])
            ->extract('id')
            ->first();

        //Cannot delete a default group
        $this->post($url + [$id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('You cannot delete this users group');

        $id = $this->UsersGroups->find()
            ->where(['id >' => 3, 'user_count >' => 0])
            ->extract('id')
            ->first();

        $this->post($url + [$id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);

        $id = $this->UsersGroups->find()
            ->where(['id >' => 3, 'user_count' => 0])
            ->extract('id')
            ->first();

        $this->post($url + [$id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
    }
}
