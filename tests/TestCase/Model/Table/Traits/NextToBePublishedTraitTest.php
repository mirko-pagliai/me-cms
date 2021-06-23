<?php
declare(strict_types=1);

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
use MeCms\TestSuite\TestCase;

/**
 * NextToBePublishedTraitTest class
 */
class NextToBePublishedTraitTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.Users',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Posts = $this->Posts ?: $this->getTable('MeCms.Posts');
    }

    /**
     * Test for `getNextToBePublished()` and `setNextToBePublished()` methods
     * @test
     */
    public function testGetNextToBePublishedAndSetNextToBePublished(): void
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
