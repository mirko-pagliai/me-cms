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
 * PageValidatorTest class
 */
class PageValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

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
        'plugin.me_cms.pages',
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

        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');

        $this->example = [
            'category_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->Pages->newEntity($this->example)->getErrors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $this->Pages->newEntity($copy)->getErrors());
        }
    }

    /**
     * Test validation for `category_id` property
     * @test
     */
    public function testValidationForCategoryId()
    {
        $this->example['category_id'] = 'string';
        $this->assertEquals([
            'category_id' => ['naturalNumber' => 'You have to select a valid option'],
        ], $this->Pages->newEntity($this->example)->getErrors());
    }
}
