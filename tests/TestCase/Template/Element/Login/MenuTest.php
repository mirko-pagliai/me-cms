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

namespace MeCms\Test\TestCase\Template\Element\Login;

use Cake\Core\Configure;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * MenuTest class
 */
class MenuTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Users',
    ];

    /**
     * Test on `login` action
     * @test
     */
    public function testLogin(): void
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
    public function testSignup(): void
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
    public function testActivationResend(): void
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
    public function testPasswordForgot(): void
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
    public function testDisabledOptions(): void
    {
        Configure::write('MeCms.users', ['signup' => false, 'reset_password' => false]);
        $this->get(['_name' => 'login']);
        $this->assertResponseNotContains('Sign up</a>');
        $this->assertResponseNotContains('Resend activation email</a>');
        $this->assertResponseNotContains('Forgot your password?</a>');

        //Account does not need to be activated by the user
        Configure::write('MeCms.users', ['signup' => true, 'activation' => 0]);
        $this->get(['_name' => 'login']);
        $this->assertResponseContains('Sign up</a>');
        $this->assertResponseNotContains('Resend activation email</a>');
    }
}
