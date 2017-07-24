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
 * TagValidatorTest class
 */
class TagValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Tags;

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
        'plugin.me_cms.tags',
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

        $this->Tags = TableRegistry::get(ME_CMS . '.Tags');

        $this->example = [
            'tag' => 'my tag',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->Tags->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `tag` property
     * @test
     */
    public function testValidationForTag()
    {
        foreach (['AbC', 'ab_c', 'ab-c', 'abc$'] as $value) {
            $this->example['tag'] = $value;
            $this->assertEquals([
                'tag' => ['validTagChars' => 'Allowed chars: lowercase letters, numbers, space'],
            ], $this->Tags->newEntity($this->example)->getErrors());
        }

        foreach (['ab', str_repeat('a', 31)] as $value) {
            $this->example['tag'] = $value;
            $this->assertEquals([
                'tag' => ['validTagLength' => 'Must be between 3 and 30 chars'],
            ], $this->Tags->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 30)] as $value) {
            $this->example['tag'] = $value;
            $this->assertEmpty($this->Tags->newEntity($this->example)->getErrors());
        }
    }
}
