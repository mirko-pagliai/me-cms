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

        $this->Banners = TableRegistry::get('MeCms.Banners');
        $this->BannersPositions = TableRegistry::get('MeCms.BannersPositions');
        $this->Posts = TableRegistry::get('MeCms.Posts');
        $this->Users = TableRegistry::get('MeCms.Users');

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
        $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
        $this->assertEquals(['user_id' => ['naturalNumber' => 'You have to select a valid option']], $errors);
    }

    /**
     * Test validation for `email` property
     * @test
     */
    public function testValidationForEmail()
    {
        foreach (['string', 'invalid@test', '@test.com', 'invalid@.com'] as $value) {
            $this->example['Users']['email'] = $value;
            $errors = $this->Users->newEntity($this->example['Users'])->errors();
            $this->assertEquals(['email' => ['email' => 'You have to enter a valid value']], $errors);
        }

        $this->example['Users']['email'] = 'example' . str_repeat('a', 85) . '@test.com';
        $errors = $this->Users->newEntity($this->example['Users'])->errors();
        $this->assertEquals(['email' => ['maxLength' => 'Must be at most 100 chars']], $errors);

        $this->example['Users']['email'] = 'example' . str_repeat('a', 84) . '@test.com';
        $errors = $this->Users->newEntity($this->example['Users'])->errors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `first_name` property
     * @test
     */
    public function testValidationForFirstNameAndLastName()
    {
        $copy = $this->example['Users'];

        foreach (['first_name', 'last_name'] as $field) {
            foreach (['Ab', 'A' . str_repeat('a', 40)] as $value) {
                $copy[$field] = $value;
                $errors = $this->Users->newEntity($copy)->errors();
                $this->assertEquals([$field => ['lengthBetween' => 'Must be between 3 and 40 chars']], $errors);
            }

            foreach (['Abc', 'A' . str_repeat('a', 39)] as $value) {
                $copy[$field] = $value;
                $errors = $this->Users->newEntity($copy)->errors();
                $this->assertEmpty($errors);
            }

            foreach (['abc', 'Ab-c', 'Ab$', 'abC'] as $value) {
                $copy[$field] = $value;
                $errors = $this->Users->newEntity($copy)->errors();
                $this->assertEquals([
                    $field => [
                        'personName' => 'Allowed chars: letters, apostrophe, space. Has to begin with a capital letter',
                    ],
                ], $errors);
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
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEquals(['title' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['Posts']['title'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        $this->example['Banners']['filename'] = str_repeat('a', 252) . '.gif';
        $errors = $this->Banners->newEntity($this->example['Banners'])->errors();
        $this->assertEquals(['filename' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $this->example['Banners']['filename'] = str_repeat('a', 251) . '.gif';
        $errors = $this->Banners->newEntity($this->example['Banners'])->errors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForSubtitle()
    {
        foreach (['ab', str_repeat('a', 151)] as $value) {
            $this->example['Posts']['subtitle'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEquals(['subtitle' => ['lengthBetween' => 'Must be between 3 and 150 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 150)] as $value) {
            $this->example['Posts']['subtitle'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `slug` property
     * @test
     */
    public function testValidationForSlug()
    {
        $this->example['Posts']['slug'] = str_repeat('a', 101);
        $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
        $this->assertEquals(['slug' => ['maxLength' => 'Must be at most 100 chars']], $errors);

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['Posts']['slug'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEmpty($errors);
        }

        foreach (['Abc', 'ab_c', 'ab$'] as $value) {
            $this->example['Posts']['slug'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEquals(['slug' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash']], $errors);
        }
    }

    /**
     * Test validation for `priority` property
     * @test
     */
    public function testValidationForPriority()
    {
        foreach (range(1, 5) as $value) {
            $this->example['Posts']['priority'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEmpty($errors);
        }

        foreach ([0, 6, 'string'] as $value) {
            $this->example['Posts']['priority'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEquals(['priority' => ['range' => 'You have to select a valid option']], $errors);
        }
    }

    /**
     * Test validation for `description` property
     * @test
     */
    public function testValidationForDescription()
    {
        $this->example['BannersPositions']['description'] = str_repeat('a', 256);
        $errors = $this->BannersPositions->newEntity($this->example['BannersPositions'])->errors();
        $this->assertEquals(['description' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $this->example['BannersPositions']['description'] = str_repeat('a', 255);
        $errors = $this->BannersPositions->newEntity($this->example['BannersPositions'])->errors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `active` property
     * @test
     */
    public function testValidationForActive()
    {
        foreach ([true, false] as $value) {
            $this->example['Posts']['active'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEmpty($errors);
        }

        $this->example['Posts']['active'] = 'string';
        $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
        $this->assertEquals(['active' => ['boolean' => 'You have to select a valid option']], $errors);
    }

    /**
     * Test validation for `created` property
     * @test
     */
    public function testValidationForCreated()
    {
        foreach ([time(), 'string', true, false] as $value) {
            $this->example['Posts']['created'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEquals(['created' => ['datetime' => 'You have to enter a valid value']], $errors);
        }

        foreach ([
            '2016-01-16 19:09',
            '2016-01-16 19:09:00',
        ] as $value) {
            $this->example['Posts']['created'] = $value;
            $errors = $this->Posts->newEntity($this->example['Posts'])->errors();
            $this->assertEmpty($errors);
        }
    }
}
