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
namespace MeCms\Test\TestCase\Model\Validation;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * UserValidatorTest class
 */
class UserValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Example data
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->example = [
            'group_id' => 1,
            'email' => 'example@test.com',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'username' => 'myusername',
            'password' => 'mypassword1!',
            'password_repeat' => 'mypassword1!',
        ];

        $this->Users = TableRegistry::get('MeCms.Users');
    }

    /**
     * Test validation for `group_id` property
     * @test
     */
    public function testValidationForGroupId()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['group_id'] = 'string';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['group_id' => ['naturalNumber' => 'You have to select a valid option']], $errors);

        unset($this->example['group_id']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['group_id' => ['_required' => 'This field is required']], $errors);
    }

    /**
     * Test validation for `username` property
     * @test
     */
    public function testValidationForUsername()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $expected = ['username' => ['lengthBetween' => 'Must be between 4 and 40 chars']];

        $this->example['username'] = 'abcd';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['username'] = str_repeat('a', 40);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['username'] = 'ab1';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['username'] = str_repeat('a', 41);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $expected = ['username' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash']];

        $this->example['username'] = 'Abcd';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['username'] = 'ab_cd';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['username'] = 'abcd$';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $expected = ['username' => ['usernameNotReserved' => 'This value contains a reserved word']];

        foreach (['admin', 'manager', 'root', 'supervisor', 'moderator'] as $reservedWord) {
            $this->example['username'] = $reservedWord;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals($expected, $errors);

            $this->example['username'] = 'a' . $reservedWord . 'b';
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals($expected, $errors);
        }

        unset($this->example['username']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['username' => ['_required' => 'This field is required']], $errors);
    }

    /**
     * Test validation for `email` property
     * @test
     */
    public function testValidationForEmail()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        unset($this->example['email']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['email' => ['_required' => 'This field is required']], $errors);
    }

    /**
     * Test validation for `email_repeat` property
     * @test
     */
    public function testValidationForEmailRepeat()
    {
        $this->example['email_repeat'] = $this->example['email'];
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['email_repeat'] = 'a_different_email@email.it';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['email_repeat' => ['compareWith' => 'Email addresses don\'t match']], $errors);
    }

    public function testValidationForPassword()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['password'] = $this->example['password_repeat'] = 'ab';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals([
            'password' => [
                    'minLength' => 'Must be at least 8 chars',
                    'passwordIsStrong' => 'The password should contain letters, numbers and symbols',
            ],
        ], $errors);

        $expected = ['password' => ['passwordIsStrong' => 'The password should contain letters, numbers and symbols']];

        $this->example['password'] = $this->example['password_repeat'] = 'abcdefgh';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['password'] = $this->example['password_repeat'] = '12345678';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['password'] = $this->example['password_repeat'] = '!!!!!!!!';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['password'] = $this->example['password_repeat'] = 'abcd1234';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['password'] = $this->example['password_repeat'] = 'abcd!!!!';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['password'] = $this->example['password_repeat'] = '1234!!!!';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals($expected, $errors);

        $this->example['password'] = $this->example['password_repeat'] = 'abcdef1!';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        unset($this->example['password'], $this->example['password_repeat']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals([
            'password' => [
                    '_required' => 'This field is required',
            ],
            'password_repeat' => [
                    '_required' => 'This field is required',
            ],
        ], $errors);
    }

    /**
     * Test validation for `password_repeat` property
     * @test
     */
    public function testValidationForPasswordRepeat()
    {
        $this->example['password_repeat'] = $this->example['password'];
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['password_repeat'] = 'aDifferentPassword';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['password_repeat' => ['compareWith' => 'Passwords don\'t match']], $errors);
    }

    /**
     * Test validation for `old_password` property
     * @test
     */
    public function testValidationForOldPassword()
    {
        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test validation for `first_name` property
     * @test
     */
    public function testValidationForFirstName()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        unset($this->example['first_name']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['first_name' => ['_required' => 'This field is required']], $errors);
    }

    /**
     * Test validation for `last_name` property
     * @test
     */
    public function testValidationForLastName()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        unset($this->example['last_name']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['last_name' => ['_required' => 'This field is required']], $errors);
    }

    /**
     * Test validation for `banned` property
     * @test
     */
    public function testValidationForBanned()
    {
        $this->example['banned'] = true;
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['banned'] = false;
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        $this->example['banned'] = 'string';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['banned' => ['boolean' => 'You have to select a valid option']], $errors);
    }
}
