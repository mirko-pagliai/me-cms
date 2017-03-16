<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use MeCms\Model\Entity\Tag;

/**
 * TagTest class
 */
class TagTest extends TestCase
{
    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('MeCms\Model\Entity\Tag', new Tag);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $entity = new Tag();

        $this->assertFalse($entity->isAccessible('id'));
        $this->assertFalse($entity->isAccessible('post_count'));
        $this->assertFalse($entity->isAccessible('modified'));
    }

    /**
     * Test for `_getSlug()` method
     * @test
     */
    public function testSlugGetMutator()
    {
        $entity = new Tag();

        $this->assertNull($entity->slug);

        $entity->tag = 'This is a tag';
        $this->assertEquals('this-is-a-tag', $entity->slug);

        $entity->tag = 'MY_TAG.a!';
        $this->assertEquals('my-tag-a', $entity->slug);
    }
}
