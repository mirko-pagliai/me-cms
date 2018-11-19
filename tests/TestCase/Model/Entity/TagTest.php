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

use MeCms\TestSuite\EntityTestCase;

/**
 * TagTest class
 */
class TagTest extends EntityTestCase
{
    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'post_count', 'modified']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField('slug');
    }

    /**
     * Test for `_getSlug()` method
     * @test
     */
    public function testSlugGetMutator()
    {
        $this->Entity->tag = 'This is a tag';
        $this->assertEquals('this-is-a-tag', $this->Entity->slug);

        $this->Entity->tag = 'MY_TAG.a!';
        $this->assertEquals('my-tag-a', $this->Entity->slug);
    }
}
