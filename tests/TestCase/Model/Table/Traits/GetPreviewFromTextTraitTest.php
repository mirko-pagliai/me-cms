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
namespace MeCms\Test\TestCase\Model\Table\Traits;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MeCms\Model\Table\PostsTable;
use MeTools\Utility\Youtube;
use Reflection\ReflectionTrait;

/**
 * GetPreviewFromTextTraitTest class
 */
class GetPreviewFromTextTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

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
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Posts);
    }

    /**
     * Test for `firstImage()` method
     * @test
     */
    public function testFirstImage()
    {
        $firstImageMethod = function ($text) {
            return $this->invokeMethod($this->Posts, 'firstImage', [$text]);
        };

        $this->assertFalse($firstImageMethod('Text'));

        $this->assertFalse($firstImageMethod('<img src=\'\'>'));
        $this->assertFalse($firstImageMethod('<img src=\'a\'>'));
        $this->assertFalse($firstImageMethod('<img src=\'a.a\'>'));
        $this->assertFalse($firstImageMethod('<img src=\'data:\'>'));
        $this->assertFalse($firstImageMethod('<img src=\'text.txt\'>'));

        $this->assertEquals('image.jpg', $firstImageMethod('<img src=\'image.jpg\'>'));
        $this->assertEquals('image.jpeg', $firstImageMethod('<img src=\'image.jpeg\'>'));
        $this->assertEquals('image.gif', $firstImageMethod('<img src=\'image.gif\'>'));
        $this->assertEquals('image.png', $firstImageMethod('<img src=\'image.png\'>'));

        $this->assertEquals('IMAGE.jpg', $firstImageMethod('<img src=\'IMAGE.jpg\'>'));
        $this->assertEquals('image.JPG', $firstImageMethod('<img src=\'image.JPG\'>'));
        $this->assertEquals('IMAGE.JPG', $firstImageMethod('<img src=\'IMAGE.JPG\'>'));

        $this->assertEquals('/image.jpg', $firstImageMethod('<img src=\'/image.jpg\'>'));
        $this->assertEquals('subdir/image.jpg', $firstImageMethod('<img src=\'subdir/image.jpg\'>'));
        $this->assertEquals('/subdir/image.jpg', $firstImageMethod('<img src=\'/subdir/image.jpg\'>'));

        //Some attributes
        $this->assertEquals('image.jpg', $firstImageMethod('<img alt=\'\' src=\'image.jpg\'>'));
        $this->assertEquals('image.jpg', $firstImageMethod('<img alt="" src="image.jpg">'));
        $this->assertEquals('image.jpg', $firstImageMethod('<img alt=\'\' class=\'my-class\' src=\'image.jpg\'>'));
        $this->assertEquals('image.jpg', $firstImageMethod('<img alt="" class="my-class" src="image.jpg">'));

        //Two images
        $this->assertEquals('image.jpg', $firstImageMethod('<img src=\'image.jpg\' /><img src=\'image.gif\' />'));
        $this->assertEquals('image.jpg', $firstImageMethod('<img src=\'image.jpg\'><img src=\'image.gif\'>'));
        $this->assertEquals('image.jpg', $firstImageMethod('<img src=\'image.jpg\'> Text <img src=\'image.gif\'>'));

        $expected = 'http://example.com/image.jpg';

        $this->assertEquals($expected, $firstImageMethod('<img src=\'http://example.com/image.jpg\'>'));
        $this->assertEquals($expected, $firstImageMethod('<img src=\'http://example.com/image.jpg\' />'));
        $this->assertEquals($expected, $firstImageMethod('<img src=\'http://example.com/image.jpg\' />Text'));
        $this->assertEquals($expected, $firstImageMethod('<img src=\'http://example.com/image.jpg\' /> Text'));

        $this->assertEquals('ftp://example.com/image.jpg', $firstImageMethod('<img src=\'ftp://example.com/image.jpg\'>'));
        $this->assertEquals('https://example.com/image.jpg', $firstImageMethod('<img src=\'https://example.com/image.jpg\'>'));
        $this->assertEquals('http://www.example.com/image.jpg', $firstImageMethod('<img src=\'http://www.example.com/image.jpg\'>'));
    }

    /**
     * Test for `getPreviewSize()` method
     * @test
     */
    public function testGetPreviewSize()
    {
        $result = $this->invokeMethod($this->Posts, 'getPreviewSize', [WWW_ROOT . 'img' . DS . 'image.jpg']);
        $this->assertEquals([400, 400], $result);
    }

    /**
     * Test for `getPreview()` method
     * @test
     */
    public function testGetPreview()
    {
        $getPreviewMethod = function ($text) {
            return $this->invokeMethod($this->Posts, 'getPreview', [$text]);
        };

        $this->Posts = $this->getMockBuilder(PostsTable::class)
            ->setMethods(['getPreviewSize'])
            ->getMock();

        $this->Posts->method('getPreviewSize')
            ->will($this->returnValue([400, 300]));

        $this->assertNull($getPreviewMethod(null));

        $this->assertNull($getPreviewMethod('text'));

        //No existing file
        $this->assertNull($getPreviewMethod('<img src=\'' . WWW_ROOT . 'img' . DS . 'noExisting.jpg' . '\' />'));

        $result = $getPreviewMethod(
            '<img src=\'https://raw.githubusercontent.com/mirko-pagliai/me-cms/master/tests/test_app/TestApp/webroot/img/image.jpg\' />'
        );
        $this->assertEquals([
            'preview' => 'https://raw.githubusercontent.com/mirko-pagliai/me-cms/master/tests/test_app/TestApp/webroot/img/image.jpg',
            'width' => 400,
            'height' => 300,
        ], $result);

        foreach ([
            'image.jpg',
            WWW_ROOT . 'img' . DS . 'image.jpg',
        ] as $image) {
            $result = $getPreviewMethod('<img src=\'' . $image . '\' />');
            $this->assertEquals(['preview', 'width', 'height'], array_keys($result));
            $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+/', $result['preview']);
            $this->assertEquals(400, $result['width']);
            $this->assertEquals(300, $result['height']);
        }

        $result = $getPreviewMethod('[youtube]6z4KK7RWjmk[/youtube]');
        $this->assertEquals([
            'preview' => Youtube::getPreview('6z4KK7RWjmk'),
            'width' => 400,
            'height' => 300,
        ], $result);
    }
}
