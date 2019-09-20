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

use Cake\Core\Configure;
use Cake\Event\Event;
use MeCms\Test\TestCase\Controller\AppControllerTest as BaseAppControllerTest;

/**
 * AppControllerTest class
 */
class AppControllerTest extends BaseAppControllerTest
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Controller->setRequest($this->Controller->getRequest()->withParam('prefix', ADMIN_PREFIX));
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        Configure::write('MeCms.admin.records', 7);

        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEmpty($this->Controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $this->Controller->paginate);
        $this->assertEquals('MeCms.View/Admin', $this->Controller->viewBuilder()->getClassName());
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
    }
}
