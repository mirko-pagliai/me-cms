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
 * BannersPositionValidatorTest class
 */
class BannersPositionValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\BannersPositionsTable
     */
    protected $BannersPositions;

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
        'plugin.me_cms.banners_positions',
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

        $this->BannersPositions = TableRegistry::get(ME_CMS . '.BannersPositions');

        $this->example = [
            'title' => 'my-title',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->BannersPositions->newEntity($this->example)->getErrors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $this->BannersPositions->newEntity($copy)->getErrors());
        }
    }

    /**
     * Test validation for `title` property
     * @test
     */
    public function testValidatorForTitle()
    {
        foreach (['ab', str_repeat('a', 101)] as $value) {
            $this->example['title'] = $value;
            $this->assertEquals([
                'title' => ['lengthBetween' => 'Must be between 3 and 100 chars'],
            ], $this->BannersPositions->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['title'] = $value;
            $this->assertEmpty($this->BannersPositions->newEntity($this->example)->getErrors());
        }
    }
}
