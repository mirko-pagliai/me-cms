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
namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\Model\Entity\Tag;
use MeCms\TestSuite\EntityTestCase;

/**
 * PostTest class
 */
class PostTest extends EntityTestCase
{
    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'preview', 'modified']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField(['plain_text', 'tags_as_string']);
    }

    /**
     * Test for `_getPlainText()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $expected = 'This is a text';

        $this->Entity->text = 'This is a [readmore /]text';
        $this->assertEquals($expected, $this->Entity->plain_text);
        $this->assertNotEquals($this->Entity->text, $this->Entity->plain_text);

        $this->Entity->text = $expected;
        $this->assertEquals($expected, $this->Entity->plain_text);
        $this->assertEquals($this->Entity->text, $this->Entity->plain_text);
    }

    /**
     * Test for `_getTagsAsString()` method
     * @test
     */
    public function testTagsAsStringGetMutator()
    {
        $tags[] = new Tag(['tag' => 'cat']);
        $tags[] = new Tag(['tag' => 'dog']);
        $tags[] = new Tag(['tag' => 'bird']);

        $this->assertNull($this->Entity->tags_as_string);

        $this->Entity->tags = $tags;
        $this->assertEquals('cat, dog, bird', $this->Entity->tags_as_string);

        array_pop($tags);
        $this->Entity->tags = $tags;
        $this->assertEquals('cat, dog', $this->Entity->tags_as_string);

        array_pop($tags);
        $this->Entity->tags = $tags;
        $this->assertEquals('cat', $this->Entity->tags_as_string);
    }
}
