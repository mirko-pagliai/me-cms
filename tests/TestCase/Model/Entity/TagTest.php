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
use MeTools\TestSuite\TestCase;

/**
 * TagTest class
 */
class TagTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\Tag
     */
    protected $Tag;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Tag = new Tag;
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->Tag->isAccessible('id'));
        $this->assertFalse($this->Tag->isAccessible('post_count'));
        $this->assertFalse($this->Tag->isAccessible('modified'));
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['slug'], $this->Tag->getVirtual());
    }

    /**
     * Test for `_getSlug()` method
     * @test
     */
    public function testSlugGetMutator()
    {
        $this->assertNull($this->Tag->slug);

        $this->Tag->tag = 'This is a tag';
        $this->assertEquals('this-is-a-tag', $this->Tag->slug);

        $this->Tag->tag = 'MY_TAG.a!';
        $this->assertEquals('my-tag-a', $this->Tag->slug);
    }
}
