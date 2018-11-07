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
namespace MeCms\Test\TestCase\Model\Table\Traits;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

/**
 * NextToBePublishedTraitTest class
 */
class NextToBePublishedTraitTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
        'plugin.me_cms.Users',
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

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');
    }

    /**
     * Test for `getNextToBePublished()` and `setNextToBePublished()` methods
     * @test
     */
    public function testGetNextToBePublishedAndSetNextToBePublished()
    {
        //Creates a record with a future publication time (1 hours)
        $future = new Time('+1 hours');

        $entity = $this->Posts->newEntity([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Future record',
            'slug' => 'future-record',
            'text' => 'Example text',
            'created' => $future,
        ]);

        $this->assertNotEmpty($this->Posts->save($entity));
        $this->assertEquals($future->toUnixString(), $this->Posts->setNextToBePublished());
        $this->assertEquals($future->toUnixString(), $this->Posts->getNextToBePublished());

        //Creates another record with a future publication time (30 minuts)
        //This record takes precedence over the previous
        $future = new Time('+30 minutes');

        $entity = $this->Posts->newEntity([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Another future record',
            'slug' => 'another-future-record',
            'text' => 'Example text',
            'created' => $future,
        ]);

        $this->assertNotEmpty($this->Posts->save($entity));
        $this->assertEquals($future->toUnixString(), $this->Posts->setNextToBePublished());
        $this->assertEquals($future->toUnixString(), $this->Posts->getNextToBePublished());
    }
}
