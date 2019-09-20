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
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        //Sets some configuration values
        Configure::write('MeCms.admin.records', 7);

        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field'])
            ->withParam('prefix', ADMIN_PREFIX);
        $controller->beforeFilter(new Event('myEvent'));
        $this->assertEmpty($controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $controller->paginate);
        $this->assertEquals('MeCms.View/Admin', $controller->viewBuilder()->getClassName());
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->Controller->setRequest($this->Controller->getRequest()->withParam('prefix', ADMIN_PREFIX));

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);
    }
}
