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
        'plugin.me_cms.users_groups',
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

        $this->Users = TableRegistry::get('MeCms.Users');

        $this->example = [
            'group_id' => 1,
            'email' => 'example@test.com',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'username' => 'myusername',
            'password' => 'mypassword1!',
            'password_repeat' => 'mypassword1!',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        foreach ($this->example as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $errors = $this->Users->newEntity($copy)->errors();
            $this->assertEquals([$key => ['_required' => 'This field is required']], $errors);
        }
    }

    /**
     * Test validation for `group_id` property
     * @test
     */
    public function testValidationForGroupId()
    {
        $this->example['group_id'] = 'string';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['group_id' => ['naturalNumber' => 'You have to select a valid option']], $errors);
    }

    /**
     * Test validation for `username` property
     * @test
     */
    public function testValidationForUsername()
    {
        foreach (['abcd', str_repeat('a', 40)] as $value) {
            $this->example['username'] = $value;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEmpty($errors);
        }

        foreach (['ab1', str_repeat('a', 41)] as $value) {
            $this->example['username'] = $value;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals(['username' => ['lengthBetween' => 'Must be between 4 and 40 chars']], $errors);
        }

        foreach (['Abcd', 'ab_cd', 'abcd$'] as $value) {
            $this->example['username'] = $value;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals(['username' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash']], $errors);
        }

        $expected = ['username' => ['usernameNotReserved' => 'This value contains a reserved word']];

        foreach (['admin', 'manager', 'root', 'supervisor', 'moderator'] as $value) {
            $this->example['username'] = $value;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals($expected, $errors);

            $this->example['username'] = 'a' . $value . 'b';
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals($expected, $errors);
        }
    }

    /**
     * Test validation for `username` property, testing that is unique
     * @test
     */
    public function testValidationForUsernameIsUnique()
    {
        $entity = $this->Users->newEntity($this->example);
        $this->assertNotEmpty($this->Users->save($entity));

        //Saves again the same entity
        $this->example['email'] = 'newmail@example.com';
        $entity = $this->Users->newEntity($this->example);
        $this->assertFalse($this->Users->save($entity));
        $this->assertEquals(['username' => ['_isUnique' => 'This value is already used']], $entity->errors());
    }

    /**
     * Test validation for `email` property, testing that is unique
     * @test
     */
    public function testValidationForEmailIsUnique()
    {
        $entity = $this->Users->newEntity($this->example);
        $this->assertNotEmpty($this->Users->save($entity));

        //Saves again the same entity
        $this->example['username'] = 'new-username';
        $entity = $this->Users->newEntity($this->example);
        $this->assertFalse($this->Users->save($entity));
        $this->assertEquals(['email' => ['_isUnique' => 'This value is already used']], $entity->errors());
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

    /**
     * Test validation for `password` property
     * @test
     */
    public function testValidationForPassword()
    {
        $this->example['password'] = $this->example['password_repeat'] = 'ab';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['password' => ['minLength' => 'Must be at least 8 chars']], $errors);

        foreach (['abcdefgh', '12345678', '!!!!!!!!', 'abcd1234', 'abcd!!!!', '1234!!!!'] as $value) {
            $this->example['password'] = $this->example['password_repeat'] = $value;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEquals([
                'password' => [
                    'passwordIsStrong' => 'The password should contain letters, numbers and symbols',
                ],
            ], $errors);
        }

        unset($this->example['password'], $this->example['password_repeat']);
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals([
            'password' => ['_required' => 'This field is required'],
            'password_repeat' => ['_required' => 'This field is required'],
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
     * Test validation for `banned` property
     * @test
     */
    public function testValidationForBanned()
    {
        foreach ([true, false] as $value) {
            $this->example['banned'] = $value;
            $errors = $this->Users->newEntity($this->example)->errors();
            $this->assertEmpty($errors);
        }

        $this->example['banned'] = 'string';
        $errors = $this->Users->newEntity($this->example)->errors();
        $this->assertEquals(['banned' => ['boolean' => 'You have to select a valid option']], $errors);
    }
}
