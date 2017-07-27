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
use MeCms\TestSuite\IntegrationTestCase;

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

        $this->get(['_name' => 'login', '?' => ['redirect' => 'action']]);
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
