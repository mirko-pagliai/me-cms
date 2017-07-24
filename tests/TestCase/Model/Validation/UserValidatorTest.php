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

        $this->Users = TableRegistry::get(ME_CMS . '.Users');

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
        $this->assertEmpty($this->Users->newEntity($this->example)->getErrors());

        foreach (array_keys($this->example) as $key) {
            if ($key === 'password') {
                continue;
            }

            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $this->Users->newEntity($copy)->getErrors());
        }
    }

    /**
     * Test validation for `group_id` property
     * @test
     */
    public function testValidationForGroupId()
    {
        $this->example['group_id'] = 'string';
        $this->assertEquals([
            'group_id' => ['naturalNumber' => 'You have to select a valid option'],
        ], $this->Users->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `username` property
     * @test
     */
    public function testValidationForUsername()
    {
        foreach (['Abcd', 'ab_cd', 'abcd$'] as $value) {
            $this->example['username'] = $value;
            $this->assertEquals([
                'username' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash'],
            ], $this->Users->newEntity($this->example)->getErrors());
        }

        $expected = ['username' => ['usernameNotReserved' => 'This value contains a reserved word']];

        foreach (['admin', 'manager', 'root', 'supervisor', 'moderator'] as $value) {
            $this->example['username'] = $value;
            $this->assertEquals($expected, $this->Users->newEntity($this->example)->getErrors());

            $this->example['username'] = 'a' . $value . 'b';
            $this->assertEquals($expected, $this->Users->newEntity($this->example)->getErrors());
        }

        foreach (['ab1', str_repeat('a', 41)] as $value) {
            $this->example['username'] = $value;
            $this->assertEquals([
                'username' => ['lengthBetween' => 'Must be between 4 and 40 chars'],
            ], $this->Users->newEntity($this->example)->getErrors());
        }

        foreach (['abcd', str_repeat('a', 40)] as $value) {
            $this->example['username'] = $value;
            $this->assertEmpty($this->Users->newEntity($this->example)->getErrors());
        }
    }

    /**
     * Test validation for `email_repeat` property
     * @test
     */
    public function testValidationForEmailRepeat()
    {
        $this->example['email_repeat'] = 'a_different_email@email.it';
        $this->assertEquals([
            'email_repeat' => ['compareWith' => 'Email addresses don\'t match'],
        ], $this->Users->newEntity($this->example)->getErrors());

        $this->example['email_repeat'] = $this->example['email'];
        $this->assertEmpty($this->Users->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `password` property
     * @test
     */
    public function testValidationForPassword()
    {
        $this->example['password'] = $this->example['password_repeat'] = 'ab';
        $this->assertEquals([
            'password' => ['minLength' => 'Must be at least 8 chars'],
        ], $this->Users->newEntity($this->example)->getErrors());

        foreach (['abcdefgh', '12345678', '!!!!!!!!', 'abcd1234', 'abcd!!!!', '1234!!!!'] as $value) {
            $this->example['password'] = $this->example['password_repeat'] = $value;
            $this->assertEquals([
                'password' => ['passwordIsStrong' => 'The password should contain letters, numbers and symbols'],
            ], $this->Users->newEntity($this->example)->getErrors());
        }

        unset($this->example['password'], $this->example['password_repeat']);
        $this->assertEquals([
            'password' => ['_required' => 'This field is required'],
            'password_repeat' => ['_required' => 'This field is required'],
        ], $this->Users->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `password_repeat` property
     * @test
     */
    public function testValidationForPasswordRepeat()
    {
        $this->example['password_repeat'] = 'aDifferentPassword';
        $this->assertEquals([
            'password_repeat' => ['compareWith' => 'Passwords don\'t match']
        ], $this->Users->newEntity($this->example)->getErrors());

        $this->example['password_repeat'] = $this->example['password'];
        $this->assertEmpty($this->Users->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `old_password` property
     * @test
     */
    public function testValidationForOldPassword()
    {
        //Saves the entity
        $entity = $this->Users->newEntity($this->example);
        $this->assertNotEmpty($this->Users->save($entity));

        $this->example['password_old'] = $this->example['password'] . 'aaa';
        $this->assertEquals([
            'password_old' => ['oldPasswordIsRight' => 'The old password is wrong'],
        ], $this->Users->patchEntity($entity, $this->example)->getErrors());

        $this->example['password_old'] = $this->example['password'];
        $this->assertEmpty($this->Users->patchEntity($entity, $this->example)->getErrors());
    }

    /**
     * Test validation for `banned` property
     * @test
     */
    public function testValidationForBanned()
    {
        $this->example['banned'] = 'string';
        $this->assertEquals([
            'banned' => ['boolean' => 'You have to select a valid option'],
        ], $this->Users->newEntity($this->example)->getErrors());

        foreach ([true, false] as $value) {
            $this->example['banned'] = $value;
            $this->assertEmpty($this->Users->newEntity($this->example)->getErrors());
        }
    }
}
