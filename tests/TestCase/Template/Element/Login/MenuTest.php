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

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestCase;

/**
 * MenuTest class
 */
class MenuTest extends IntegrationTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users',
    ];

    /**
     * Test on `login` action
     * @test
     */
    public function testLogin()
    {
        $this->get(['_name' => 'login']);
        $this->assertResponseNotContains('Login</a>');
        $this->assertResponseContains('Sign up</a>');
        $this->assertResponseContains('Resend activation email</a>');
        $this->assertResponseContains('Forgot your password?</a>');
    }

    /**
     * Test on `signup` action
     * @test
     */
    public function testSignup()
    {
        $this->get(['_name' => 'signup']);
        $this->assertResponseContains('Login</a>');
        $this->assertResponseNotContains('Sign up</a>');
        $this->assertResponseContains('Resend activation email</a>');
        $this->assertResponseContains('Forgot your password?</a>');
    }

    /**
     * Test on `activationResend` action
     * @test
     */
    public function testActivationResend()
    {
        $this->get(['_name' => 'activationResend']);
        $this->assertResponseContains('Login</a>');
        $this->assertResponseContains('Sign up</a>');
        $this->assertResponseNotContains('Resend activation email</a>');
        $this->assertResponseContains('Forgot your password?</a>');
    }

    /**
     * Test on `passwordForgot` action
     * @test
     */
    public function testPasswordForgot()
    {
        $this->get(['_name' => 'passwordForgot']);
        $this->assertResponseContains('Login</a>');
        $this->assertResponseContains('Sign up</a>');
        $this->assertResponseContains('Resend activation email</a>');
        $this->assertResponseNotContains('Forgot your password?</a>');
    }

    /**
     * Test with disabled options
     * @test
     */
    public function testDisabledOptions()
    {
        Configure::write(ME_CMS . '.users.signup', false);
        Configure::write(ME_CMS . '.users.reset_password', false);

        $this->get(['_name' => 'login']);
        $this->assertResponseNotContains('Sign up</a>');
        $this->assertResponseNotContains('Resend activation email</a>');
        $this->assertResponseNotContains('Forgot your password?</a>');

        Configure::write(ME_CMS . '.users.signup', true);
        Configure::write(ME_CMS . '.users.activation', 0);

        //Signup is enaled, but the account does not need to be activated by
        //  the user
        $this->get(['_name' => 'login']);
        $this->assertResponseContains('Sign up</a>');
        $this->assertResponseNotContains('Resend activation email</a>');
    }
}
