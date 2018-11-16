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

use MeTools\TestSuite\TestCase;
use MeTools\TestSuite\Traits\MockTrait;

/**
 * AppValidatorTest class
 */
class AppValidatorTest extends TestCase
{
    use MockTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Banners;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Posts;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Users;

    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * @var array
     */
    protected $example = [
        'Banners' => ['position_id' => 1, 'filename' => 'pic.jpg'],
        'Posts' => [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ],
        'Users' => [
            'group_id' => 1,
            'email' => 'example@test.com',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'username' => 'myusername',
            'password' => 'mypassword1!',
            'password_repeat' => 'mypassword1!',
        ],
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Banners',
        'plugin.me_cms.Posts',
        'plugin.me_cms.Users',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        foreach (['Banners', 'Posts', 'Users'] as $alias) {
            $this->$alias = $this->getMockForTable(sprintf('%s\Model\Table\%sTable', ME_CMS, $alias), null);
        }
    }

    /**
     * Test validation for `user_id` property
     * @test
     */
    public function testValidationForUserId()
    {
        $this->loadFixtures('Posts');
        $errors = $this->Posts->newEntity(['user_id' => 'string'] + $this->example['Posts'])->getErrors();
        $this->assertEquals(['user_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `email` property
     * @test
     */
    public function testValidationForEmail()
    {
        $this->loadFixtures('Users');

        foreach (['string', 'invalid@test', '@test.com', 'invalid@.com'] as $email) {
            $errors = $this->Users->newEntity(compact('email') + $this->example['Users'])->getErrors();
            $this->assertEquals(['email' => ['email' => I18N_ENTER_VALID_VALUE]], $errors);
        }

        $errors = $this->Users->newEntity(['email' => str_repeat('a', 92) . '@test.com'] + $this->example['Users'])->getErrors();
        $this->assertEquals(['email' => ['maxLength' => 'Must be at most 100 chars']], $errors);

        $errors = $this->Users->newEntity(['email' => str_repeat('a', 91) . '@test.com'] + $this->example['Users'])->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `first_name` property
     * @test
     */
    public function testValidationForFirstNameAndLastName()
    {
        $this->loadFixtures('Users');

        foreach (['first_name', 'last_name'] as $field) {
            foreach (['abc', 'Ab-c', 'Ab$', 'abC'] as $value) {
                $expected = [$field => ['personName' => 'Allowed chars: letters, apostrophe, space. Has to begin with a capital letter']];
                $errors = $this->Users->newEntity([$field => $value] + $this->example['Users'])->getErrors();
                $this->assertEquals($expected, $errors);
            }

            foreach (['Ab', 'A' . str_repeat('a', 40)] as $value) {
                $errors = $this->Users->newEntity([$field => $value] + $this->example['Users'])->getErrors();
                $this->assertEquals([$field => ['lengthBetween' => 'Must be between 3 and 40 chars']], $errors);
            }

            foreach (['Abc', 'A' . str_repeat('a', 39)] as $value) {
                $errors = $this->Users->newEntity([$field => $value] + $this->example['Users'])->getErrors();
                $this->assertEmpty($errors);
            }
        }
    }

    /**
     * Test validation for `title` property
     * @test
     */
    public function testValidationForTitle()
    {
        $this->loadFixtures('Posts');

        foreach (['ab', str_repeat('a', 101)] as $title) {
            $errors = $this->Posts->newEntity(compact('title') + $this->example['Posts'])->getErrors();
            $this->assertEquals(['title' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 100)] as $title) {
            $errors = $this->Posts->newEntity(compact('title') + $this->example['Posts'])->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        $this->loadFixtures('Banners');

        $errors = $this->Banners->newEntity(['filename' => str_repeat('a', 252) . '.gif'] + $this->example['Banners'])->getErrors();
        $this->assertEquals(['filename' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $errors = $this->Banners->newEntity(['filename' => str_repeat('a', 251) . '.gif'] + $this->example['Banners'])->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForSubtitle()
    {
        $this->loadFixtures('Posts');

        foreach (['ab', str_repeat('a', 151)] as $subtitle) {
            $errors = $this->Posts->newEntity(compact('subtitle') + $this->example['Posts'])->getErrors();
            $this->assertEquals(['subtitle' => ['lengthBetween' => 'Must be between 3 and 150 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 150)] as $subtitle) {
            $errors = $this->Posts->newEntity(compact('subtitle') + $this->example['Posts'])->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `slug` property
     * @test
     */
    public function testValidationForSlug()
    {
        $this->loadFixtures('Posts');

        foreach (['Abc', 'ab_c', 'ab$'] as $slug) {
            $errors = $this->Posts->newEntity(compact('slug') + $this->example['Posts'])->getErrors();
            $this->assertEquals(['slug' => ['slug' => 'Allowed chars: lowercase letters, numbers, dash']], $errors);
        }

        $errors = $this->Posts->newEntity(['slug' => str_repeat('a', 101)] + $this->example['Posts'])->getErrors();
        $this->assertEquals(['slug' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);

        foreach (['abc', str_repeat('a', 100)] as $slug) {
            $errors = $this->Posts->newEntity(compact('slug') + $this->example['Posts'])->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `priority` property
     * @test
     */
    public function testValidationForPriority()
    {
        $this->loadFixtures('Posts');

        foreach ([0, 6, 'string'] as $priority) {
            $errors = $this->Posts->newEntity(compact('priority') + $this->example['Posts'])->getErrors();
            $this->assertEquals(['priority' => ['range' => I18N_SELECT_VALID_OPTION]], $errors);
        }

        foreach (range(1, 5) as $priority) {
            $errors = $this->Posts->newEntity(compact('priority') + $this->example['Posts'])->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `description` property
     * @test
     */
    public function testValidationForDescription()
    {
        $this->loadFixtures('Banners');

        $errors = $this->Banners->newEntity(['description' => str_repeat('a', 256)] + $this->example['Banners'])->getErrors();
        $this->assertEquals(['description' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $errors = $this->Banners->newEntity(['description' => str_repeat('a', 255)] + $this->example['Banners'])->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `active` property
     * @test
     */
    public function testValidationForActive()
    {
        $this->loadFixtures('Posts');

        $errors = $this->Posts->newEntity(['active' => 'str'] + $this->example['Posts'])->getErrors();
        $this->assertEquals(['active' => ['boolean' => I18N_SELECT_VALID_OPTION]], $errors);

        foreach ([true, false] as $active) {
            $errors = $this->Posts->newEntity(compact('active') + $this->example['Posts'])->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `created` property
     * @test
     */
    public function testValidationForCreated()
    {
        $this->loadFixtures('Posts');

        foreach ([time(), 'string', true, false] as $created) {
            $errors = $this->Posts->newEntity(compact('created') + $this->example['Posts'])->getErrors();
            $this->assertEquals(['created' => ['datetime' => I18N_ENTER_VALID_VALUE]], $errors);
        }

        foreach (['2016-01-16 19:09', '2016-01-16 19:09:00'] as $created) {
            $errors = $this->Posts->newEntity(compact('created') + $this->example['Posts'])->getErrors();
            $this->assertEmpty($errors);
        }
    }
}
