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
use MeTools\TestSuite\TestCase;

/**
 * AppValidatorTest class
 */
class AppValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\BannersTable
     */
    protected $Banners;

    /**
     * @var \MeCms\Model\Table\BannersPositionsTable
     */
    protected $BannersPositions;

    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

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
        'plugin.me_cms.banners',
        'plugin.me_cms.banners_positions',
        'plugin.me_cms.posts',
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

        $this->Banners = TableRegistry::get(ME_CMS . '.Banners');
        $this->BannersPositions = TableRegistry::get(ME_CMS . '.BannersPositions');
        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');
        $this->Users = TableRegistry::get(ME_CMS . '.Users');

        $this->example['Banners'] = [
            'position_id' => 1,
            'filename' => 'pic.jpg',
        ];

        $this->example['BannersPositions'] = [
            'title' => 'my-title',
        ];

        $this->example['Posts'] = [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];

        $this->example['Users'] = [
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
     * Test validation for `user_id` property
     * @test
     */
    public function testValidationForUserId()
    {
        $this->example['Posts']['user_id'] = 'string';
        $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
        $this->assertEquals(['user_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `email` property
     * @test
     */
    public function testValidationForEmail()
    {
        foreach (['string', 'invalid@test', '@test.com', 'invalid@.com'] as $value) {
            $this->example['Users']['email'] = $value;
            $errors = $this->Users->newEntity($this->example['Users'])->getErrors();
            $this->assertEquals(['email' => ['email' => I18N_ENTER_VALID_VALUE]], $errors);
        }

        $this->example['Users']['email'] = str_repeat('a', 92) . '@test.com';
        $errors = $this->Users->newEntity($this->example['Users'])->getErrors();
        $this->assertEquals(['email' => ['maxLength' => 'Must be at most 100 chars']], $errors);

        $this->example['Users']['email'] = str_repeat('a', 91) . '@test.com';
        $this->assertEmpty($this->Users->newEntity($this->example['Users'])->getErrors());
    }

    /**
     * Test validation for `first_name` property
     * @test
     */
    public function testValidationForFirstNameAndLastName()
    {
        $copy = $this->example['Users'];

        foreach (['first_name', 'last_name'] as $field) {
            foreach (['abc', 'Ab-c', 'Ab$', 'abC'] as $value) {
                $copy[$field] = $value;
                $errors = $this->Users->newEntity($copy)->getErrors();
                $this->assertEquals([$field => ['personName' => 'Allowed chars: letters, apostrophe, space. Has to begin with a capital letter']], $errors);
            }

            foreach (['Ab', 'A' . str_repeat('a', 40)] as $value) {
                $copy[$field] = $value;
                $errors = $this->Users->newEntity($copy)->getErrors();
                $this->assertEquals([$field => ['lengthBetween' => 'Must be between 3 and 40 chars']], $errors);
            }

            foreach (['Abc', 'A' . str_repeat('a', 39)] as $value) {
                $copy[$field] = $value;
                $this->assertEmpty($this->Users->newEntity($copy)->getErrors());
            }

            $copy[$field] = $this->example['Users'][$field];
        }
    }

    /**
     * Test validation for `title` property
     * @test
     */
    public function testValidationForTitle()
    {
        foreach (['ab', str_repeat('a', 101)] as $value) {
            $this->example['Posts']['title'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
            $this->assertEquals(['title' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['Posts']['title'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example['Posts'])->getErrors());
        }
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        $this->example['Banners']['filename'] = str_repeat('a', 252) . '.gif';
        $errors = $this->Banners->newEntity($this->example['Banners'])->getErrors();
        $this->assertEquals(['filename' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $this->example['Banners']['filename'] = str_repeat('a', 251) . '.gif';
        $this->assertEmpty($this->Banners->newEntity($this->example['Banners'])->getErrors());
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForSubtitle()
    {
        foreach (['ab', str_repeat('a', 151)] as $value) {
            $this->example['Posts']['subtitle'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
            $this->assertEquals(['subtitle' => ['lengthBetween' => 'Must be between 3 and 150 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 150)] as $value) {
            $this->example['Posts']['subtitle'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example['Posts'])->getErrors());
        }
    }

    /**
     * Test validation for `slug` property
     * @test
     */
    public function testValidationForSlug()
    {
        foreach (['Abc', 'ab_c', 'ab$'] as $value) {
            $this->example['Posts']['slug'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
            $this->assertEquals(['slug' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash']], $errors);
        }

        $this->example['Posts']['slug'] = str_repeat('a', 101);
        $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
        $this->assertEquals(['slug' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['Posts']['slug'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example['Posts'])->getErrors());
        }
    }

    /**
     * Test validation for `priority` property
     * @test
     */
    public function testValidationForPriority()
    {
        foreach ([0, 6, 'string'] as $value) {
            $this->example['Posts']['priority'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
            $this->assertEquals(['priority' => ['range' => I18N_SELECT_VALID_OPTION]], $errors);
        }

        foreach (range(1, 5) as $value) {
            $this->example['Posts']['priority'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example['Posts'])->getErrors());
        }
    }

    /**
     * Test validation for `description` property
     * @test
     */
    public function testValidationForDescription()
    {
        $this->example['BannersPositions']['description'] = str_repeat('a', 256);
        $errors = $this->BannersPositions->newEntity($this->example['BannersPositions'])->getErrors();
        $this->assertEquals(['description' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $this->example['BannersPositions']['description'] = str_repeat('a', 255);
        $this->assertEmpty($this->BannersPositions->newEntity($this->example['BannersPositions'])->getErrors());
    }

    /**
     * Test validation for `active` property
     * @test
     */
    public function testValidationForActive()
    {
        $this->example['Posts']['active'] = 'string';
        $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
        $this->assertEquals(['active' => ['boolean' => I18N_SELECT_VALID_OPTION]], $errors);

        foreach ([true, false] as $value) {
            $this->example['Posts']['active'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example['Posts'])->getErrors());
        }
    }

    /**
     * Test validation for `created` property
     * @test
     */
    public function testValidationForCreated()
    {
        foreach ([time(), 'string', true, false] as $value) {
            $this->example['Posts']['created'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->getErrors();
            $this->assertEquals(['created' => ['datetime' => I18N_ENTER_VALID_VALUE]], $errors);
        }

        foreach (['2016-01-16 19:09', '2016-01-16 19:09:00'] as $value) {
            $this->example['Posts']['created'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example['Posts'])->getErrors());
        }
    }
}
