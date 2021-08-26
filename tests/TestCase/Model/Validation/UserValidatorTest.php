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

namespace MeCms\Test\TestCase\Model\Validation;

use MeCms\TestSuite\ValidationTestCase;

/**
 * UserValidatorTest class
 */
class UserValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = [
        'group_id' => 1,
        'email' => 'example@test.com',
        'first_name' => 'Alfa',
        'last_name' => 'Beta',
        'username' => 'myusername',
        'password' => 'mypassword1!',
        'password_repeat' => 'mypassword1!',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testAllDataAreRequired(): void
    {
        $this->assertAllDataAreRequired($this->example, ['password']);
    }

    /**
     * Test validation for `group_id` property
     * @test
     */
    public function testValidationForGroupId(): void
    {
        $errors = $this->Table->newEntity(['group_id' => 'string'] + $this->example)->getErrors();
        $this->assertEquals(['group_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `username` property
     * @test
     */
    public function testValidationForUsername(): void
    {
        foreach (['Abcd', 'ab_cd', 'abcd$'] as $username) {
            $errors = $this->Table->newEntity(compact('username') + $this->example)->getErrors();
            $this->assertEquals(['username' => ['slug' => sprintf('%s: %s', I18N_ALLOWED_CHARS, I18N_LOWERCASE_NUMBERS_DASH)]], $errors);
        }

        $expected = ['username' => ['notReservedWord' => 'This value contains a reserved word']];

        foreach (['admin', 'manager', 'root', 'supervisor', 'moderator'] as $username) {
            $errors = $this->Table->newEntity(compact('username') + $this->example)->getErrors();
            $this->assertEquals($expected, $errors);

            $errors = $this->Table->newEntity(['username' => 'a' . $username . 'b'] + $this->example)->getErrors();
            $this->assertEquals($expected, $errors);
        }

        foreach (['ab1', str_repeat('a', 41)] as $username) {
            $errors = $this->Table->newEntity(compact('username') + $this->example)->getErrors();
            $this->assertEquals(['username' => ['lengthBetween' => 'Must be between 4 and 40 chars']], $errors);
        }

        foreach (['abcd', str_repeat('a', 40)] as $username) {
            $errors = $this->Table->newEntity(compact('username') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `email_repeat` property
     * @test
     */
    public function testValidationForEmailRepeat(): void
    {
        $errors = $this->Table->newEntity(['email_repeat' => 'a_different_email@email.it'] + $this->example)->getErrors();
        $this->assertEquals(['email_repeat' => ['compareWith' => 'Email addresses don\'t match']], $errors);

        $errors = $this->Table->newEntity(['email_repeat' => $this->example['email']] + $this->example)->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `password` property
     * @test
     */
    public function testValidationForPassword(): void
    {
        $password_repeat = $password = 'ab';
        $errors = $this->Table->newEntity(compact('password', 'password_repeat') + $this->example)->getErrors();
        $this->assertEquals(['password' => ['minLength' => 'Must be at least 8 chars']], $errors);

        foreach (['abcdefgh', '12345678', '!!!!!!!!', 'abcd1234', 'abcd!!!!', '1234!!!!'] as $password) {
            $password_repeat = $password;
            $errors = $this->Table->newEntity(compact('password', 'password_repeat') + $this->example)->getErrors();
            $this->assertEquals(['password' => ['passwordIsStrong' => 'The password should contain letters, numbers and symbols']], $errors);
        }

        $copy = $this->example;
        unset($copy['password'], $copy['password_repeat']);
        $this->assertEquals([
            'password' => ['_required' => 'This field is required'],
            'password_repeat' => ['_required' => 'This field is required'],
        ], $this->Table->newEntity($copy)->getErrors());
    }

    /**
     * Test validation for `password_repeat` property
     * @test
     */
    public function testValidationForPasswordRepeat(): void
    {
        $errors = $this->Table->newEntity(['password_repeat' => 'differentPwd'] + $this->example)->getErrors();
        $this->assertEquals(['password_repeat' => ['compareWith' => 'Passwords don\'t match']], $errors);

        $errors = $this->Table->newEntity(['password_repeat' => $this->example['password']] + $this->example)->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `old_password` property
     * @test
     */
    public function testValidationForOldPassword(): void
    {
        //Saves the entity
        $entity = $this->Table->newEntity($this->example);
        $this->assertNotEmpty($this->Table->save($entity));

        $password_old = $this->example['password'] . 'aaa';
        $errors = $this->Table->patchEntity($entity, compact('password_old') + $this->example)->getErrors();
        $this->assertEquals(['password_old' => ['oldPasswordIsRight' => 'The old password is wrong']], $errors);

        $password_old = $this->example['password'];
        $errors = $this->Table->patchEntity($entity, compact('password_old') + $this->example)->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `banned` property
     * @test
     */
    public function testValidationForBanned(): void
    {
        $errors = $this->Table->newEntity(['banned' => 'str'] + $this->example)->getErrors();
        $this->assertEquals(['banned' => ['boolean' => I18N_SELECT_VALID_OPTION]], $errors);

        foreach ([true, false] as $banned) {
            $errors = $this->Table->newEntity(compact('banned') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }
}
