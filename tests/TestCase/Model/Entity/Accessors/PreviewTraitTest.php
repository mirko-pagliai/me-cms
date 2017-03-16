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
use MeCms\Model\Entity\Post;
use MeTools\Utility\Youtube;

/**
 * PreviewTraitTest class
 */
class PreviewTraitTest extends TestCase
{
    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testGetPreview()
    {
        $entity = new Post;

        $this->assertNull($entity->preview);

        $entity->text = 'This is a simple text';
        $this->assertNull($entity->preview);

        $expected = 'http://localhost/image.jpg';

        $entity->text = '<img src=\'image.jpg\' />e';
        $this->assertEquals($expected, $entity->preview);

        $entity->text = '<img src=\'image.jpg\' /> Text after image';
        $this->assertEquals($expected, $entity->preview);

        $entity->text = '<img src=\'http://localhost/image.jpg\' /> Text after image';
        $this->assertEquals($expected, $entity->preview);

        $expected = Youtube::getPreview('6z4KK7RWjmk');

        $entity->text = '[youtube]6z4KK7RWjmk[/youtube]';
        $this->assertEquals($expected, $entity->preview);

        $entity->text = '[youtube]6z4KK7RWjmk[/youtube] Text after video';
        $this->assertEquals($expected, $entity->preview);

        $entity->text = '[youtube]http://www.youtube.com/watch?v=6z4KK7RWjmk[/youtube]';
        $this->assertEquals($expected, $entity->preview);
    }
}
