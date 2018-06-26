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
use MeCms\TestSuite\ValidationTestCase;

/**
 * UserValidatorTest class
 */
class UserValidatorTest extends ValidationTestCase
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
        $this->assertAllDataAreRequired($this->Users, $this->example, ['password']);
    }

    /**
     * Test validation for `group_id` property
     * @test
     */
    public function testValidationForGroupId()
    {
        $this->example['group_id'] = 'string';
        $errors = $this->Users->newEntity($this->example)->getErrors();
        $this->assertEquals(['group_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `username` property
     * @test
     */
    public function testValidationForUsername()
    {
        foreach (['Abcd', 'ab_cd', 'abcd$'] as $value) {
            $this->example['username'] = $value;
            $errors = $this->Users->newEntity($this->example)->getErrors();
            $this->assertEquals(['username' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash']], $errors);
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
            $errors = $this->Users->newEntity($this->example)->getErrors();
            $this->assertEquals(['username' => ['lengthBetween' => 'Must be between 4 and 40 chars']], $errors);
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
        $errors = $this->Users->newEntity($this->example)->getErrors();
        $this->assertEquals(['email_repeat' => ['compareWith' => 'Email addresses don\'t match']], $errors);

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
        $errors = $this->Users->newEntity($this->example)->getErrors();
        $this->assertEquals(['password' => ['minLength' => 'Must be at least 8 chars']], $errors);

        foreach (['abcdefgh', '12345678', '!!!!!!!!', 'abcd1234', 'abcd!!!!', '1234!!!!'] as $value) {
            $this->example['password'] = $this->example['password_repeat'] = $value;
            $errors = $this->Users->newEntity($this->example)->getErrors();
            $this->assertEquals(['password' => ['passwordIsStrong' => 'The password should contain letters, numbers and symbols']], $errors);
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
        $errors = $this->Users->newEntity($this->example)->getErrors();
        $this->assertEquals(['password_repeat' => ['compareWith' => 'Passwords don\'t match']], $errors);

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
        $errors = $this->Users->patchEntity($entity, $this->example)->getErrors();
        $this->assertEquals(['password_old' => ['oldPasswordIsRight' => 'The old password is wrong']], $errors);

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
        $errors = $this->Users->newEntity($this->example)->getErrors();
        $this->assertEquals(['banned' => ['boolean' => I18N_SELECT_VALID_OPTION]], $errors);

        foreach ([true, false] as $value) {
            $this->example['banned'] = $value;
            $this->assertEmpty($this->Users->newEntity($this->example)->getErrors());
        }
    }
}
