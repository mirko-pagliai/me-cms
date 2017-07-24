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
namespace MeCms\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use MeCms\Controller\Component\AuthComponent;

/**
 * AuthComponentTest class
 */
class AuthComponentTest extends TestCase
{
    /**
     * @var \MeCms\Controller\Component\AuthComponent
     */
    public $Auth;

    /**
     * Internal method to get an Auth instance
     * @return \MeCms\Controller\Component\AuthComponent
     */
    protected function getAuthInstance()
    {
        return new AuthComponent(new ComponentRegistry(new Controller));
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Auth = $this->getAuthInstance();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Auth);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $expected = [
            'authenticate' => [
                'Form' => ['contain' => 'Groups', 'userModel' => ME_CMS . '.Users'],
            ],
            'authorize' => 'Controller',
            'ajaxLogin' => null,
            'flash' => [
                'element' => METOOLS . '.flash',
                'params' => ['class' => 'alert-danger'],
            ],
            'loginAction' => ['_name' => 'login'],
            'loginRedirect' => ['_name' => 'dashboard'],
            'logoutRedirect' => ['_name' => 'homepage'],
            'authError' => false,
            'unauthorizedRedirect' => ['_name' => 'dashboard'],
            'storage' => 'Session',
            'checkAuthIn' => 'Controller.startup',
        ];

        $this->assertEquals($expected, $this->Auth->getConfig());

        $this->Auth->setUser(['id' => 1]);
        $this->Auth->initialize([]);
        $expected['authError'] = 'You are not authorized for this action';
        $this->assertEquals($expected, $this->Auth->getConfig());
    }

    /**
     * Tests for `hasId()` method
     * @test
     */
    public function testHasId()
    {
        $this->assertFalse($this->Auth->hasId(1));

        $this->Auth->setUser(['id' => 1]);
        $this->assertTrue($this->Auth->hasId(1));
        $this->assertTrue($this->Auth->hasId([1, 2]));
        $this->assertFalse($this->Auth->hasId(2));
        $this->assertFalse($this->Auth->hasId([2, 3]));
    }

    /**
     * Tests for `isFounder()` method
     * @test
     */
    public function testIsFounder()
    {
        $this->assertFalse($this->Auth->isFounder());

        $this->Auth->setUser(['id' => 1]);
        $this->assertTrue($this->Auth->isFounder());

        $this->Auth->setUser(['id' => 2]);
        $this->assertFalse($this->Auth->isFounder());
    }

    /**
     * Tests for `isLogged()` method
     * @test
     */
    public function testIsLogged()
    {
        $this->assertFalse($this->Auth->isLogged());

        $this->Auth->setUser(['id' => 1]);
        $this->assertTrue($this->Auth->isLogged());
    }

    /**
     * Tests for `isGroup()` method
     * @test
     */
    public function testIsGroup()
    {
        $this->assertFalse($this->Auth->isGroup('admin'));

        $this->Auth->setUser(['group' => ['name' => 'admin']]);
        $this->assertTrue($this->Auth->isGroup('admin'));
        $this->assertTrue($this->Auth->isGroup(['admin', 'manager']));
        $this->assertFalse($this->Auth->isGroup(['manager', 'noExistingGroup']));
    }
}
