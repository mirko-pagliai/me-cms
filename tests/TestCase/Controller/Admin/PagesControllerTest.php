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
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\PagesController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\PagesController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages_categories',
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

        $this->Controller = new PagesController;

        $this->Pages = TableRegistry::get('MeCms.Pages');

        Cache::clear(false, $this->Pages->cache);

        $this->url = ['controller' => 'Pages', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->Pages);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
            $this->assertResponseOk();
            $this->assertNotEmpty($this->viewVariable('categories'));
        }

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertNotEmpty($this->viewVariable('categories'));

        //`indexStatics` still works
        $this->get(array_merge($this->url, ['action' => 'indexStatics']));
        $this->assertResponseOk();
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * Tests for `beforeFilter()` method, with no categories
     * @test
     */
    public function testBeforeFilterNoCategories()
    {
        //Deletes all categories
        $this->Pages->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
            $this->assertRedirect(['controller' => 'PagesCategories', 'action' => 'index']);
            $this->assertSession('You must first create a category', 'Flash.flash.0.message');
        }

        //`indexStatics` still works
        $this->get(array_merge($this->url, ['action' => 'indexStatics']));
        $this->assertResponseOk();
        $this->assertEmpty($this->viewVariable('categories'));
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        foreach (['add', 'edit'] as $action) {
            $this->Controller = new PagesController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
            $this->Controller->initialize();

            $this->assertContains('KcFinder', $this->Controller->components()->loaded());
        }
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
            'user' => false,
        ]);

        //`index` and `indexStatics` actions
        foreach (['index', 'indexStatics'] as $action) {
            $this->Controller = new PagesController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);

            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => true,
                'user' => true,
            ]);
        }

        //`delete` action
        $this->Controller = new PagesController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'delete');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }
}
