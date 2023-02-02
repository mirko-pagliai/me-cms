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

use MeCms\Model\Entity\Tag;
use MeCms\TestSuite\PostAndPageEntityTestCase;

/**
 * PostTest class
 */
class PostTest extends PostAndPageEntityTestCase
{
    /**
     * @test
     * @uses \MeCms\Model\Entity\Post::_getUrl()
     */
    public function testGetUrlVirtualField(): void
    {
        $this->assertStringEndsWith('/post/a-slug', $this->Entity->get('url'));
    }

    /**
     * @test
     * @uses \MeCms\Model\Entity\Post::_getTagsAsString()
     */
    public function testGetTagsAsStringVirtualField(): void
    {
        $tags = array_map(fn(string $tag): Tag => new Tag(compact('tag')), ['cat', 'dog', 'bird']);

        $this->assertEquals('cat, dog, bird', $this->Entity->set(compact('tags'))->get('tags_as_string'));

        array_pop($tags);
        $this->assertEquals('cat, dog', $this->Entity->set(compact('tags'))->get('tags_as_string'));

        array_pop($tags);
        $this->assertEquals('cat', $this->Entity->set(compact('tags'))->get('tags_as_string'));
    }
}
