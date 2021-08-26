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

namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\TestSuite\EntityTestCase;

/**
 * TagTest class
 */
class TagTest extends EntityTestCase
{
    /**
     * Test for fields that cannot be mass assigned
     * @test
     */
    public function testNoAccessibleProperties(): void
    {
        $this->assertHasNoAccessibleProperty(['id', 'post_count', 'modified']);
    }

    /**
     * Test for `_getSlug()` method
     * @test
     */
    public function testSlugGetMutator(): void
    {
        foreach ([
            'This is a tag' => 'this-is-a-tag',
            'MY_TAG.a!' => 'my-tag-a',
        ] as $tag => $expectedSlug) {
            $this->assertEquals($expectedSlug, $this->Entity->set('tag', $tag)->get('slug'));
        }
    }

    /**
     * Test for `_getUrl()` method
     * @test
     */
    public function testUrl(): void
    {
        $this->Entity->set('tag', 'a-tag');
        $this->assertStringEndsWith('/posts/tag/a-tag', $this->Entity->get('url'));
    }
}
