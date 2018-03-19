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

use Cake\ORM\TableRegistry;
use MeCms\Model\Table\PostsTable;
use MeTools\TestSuite\TestCase;
use MeTools\Utility\Youtube;

/**
 * GetPreviewsFromTextTraitTest class
 */
class GetPreviewsFromTextTraitTest extends TestCase
{
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
     * Test for `firstImage()` method
     * @test
     */
    public function testFirstImage()
    {
        $firstImageMethod = function () {
            return $this->invokeMethod($this->Posts, 'firstImage', func_get_args());
        };

        foreach ([
            null,
            false,
            '',
            'Text',
            '<img src=\'\'>',
            '<img src=\'a\'>',
            '<img src=\'a.a\'>',
            '<img src=\'data:\'>',
            '<img src=\'text.txt\'>',
        ] as $value) {
            $this->assertFalse($firstImageMethod($value));
        }

        //Values with some attributes
        foreach ([
            '<img alt=\'\' src=\'image.jpg\'>',
            '<img alt="" src="image.jpg">',
            '<img alt=\'\' class=\'my-class\' src=\'image.jpg\'>',
            '<img alt="" class="my-class" src="image.jpg">',
        ] as $value) {
            $this->assertEquals('image.jpg', $firstImageMethod($value));
        }

        //Remote images
        foreach ([
            '<img src=\'http://example.com/image.jpg\'>',
            '<img src=\'http://example.com/image.jpg\' />',
            '<img src=\'http://example.com/image.jpg\' />Text',
            '<img src=\'http://example.com/image.jpg\' /> Text',
        ] as $value) {
            $this->assertEquals('http://example.com/image.jpg', $firstImageMethod($value));
        }

        //Different protocols
        foreach ([
            'ftp://example.com/image.jpg',
            'https://example.com/image.jpg',
            'http://www.example.com/image.jpg',
        ] as $value) {
            $this->assertEquals($value, $firstImageMethod('<img src=\'' . $value . '\'>'));
        }

        //Different filenames
        foreach ([
            'image.jpg',
            'image.jpeg',
            'image.gif',
            'image.png',
            'IMAGE.jpg',
            'image.JPG',
            'IMAGE.JPG',
            '/image.jpg',
            'subdir/image.jpg',
            '/subdir/image.jpg',
        ] as $filename) {
            $this->assertEquals($filename, $firstImageMethod('<img src=\'' . $filename . '\'>'));
        }

        //Two images
        foreach ([
            '<img src=\'image.jpg\' /><img src=\'image.gif\' />',
            '<img src=\'image.jpg\'><img src=\'image.gif\'>',
            '<img src=\'image.jpg\'> Text <img src=\'image.gif\'>',
        ] as $value) {
            $this->assertEquals('image.jpg', $firstImageMethod($value));
        }
    }

    /**
     * Test for `getPreviewSize()` method
     * @test
     */
    public function testGetPreviewSize()
    {
        $this->assertEquals(
            [400, 400],
            $this->invokeMethod($this->Posts, 'getPreviewSize', [WWW_ROOT . 'img' . DS . 'image.jpg'])
        );
    }

    /**
     * Test for `getPreview()` method
     * @test
     */
    public function testGetPreview()
    {
        $getPreviewMethod = function () {
            return $this->invokeMethod($this->Posts, 'getPreview', func_get_args());
        };

        $this->Posts = $this->getMockForModel(PostsTable::class, ['getPreviewSize']);
        $this->Posts->method('getPreviewSize')->will($this->returnValue([400, 300]));

        $this->assertNull($getPreviewMethod(null));
        $this->assertNull($getPreviewMethod('text'));

        //No existing file
        $this->assertNull($getPreviewMethod('<img src=\'' . WWW_ROOT . 'img' . DS . 'noExisting.jpg' . '\' />'));

        $result = $getPreviewMethod(
            '<img src=\'https://raw.githubusercontent.com/mirko-pagliai/me-cms/master/tests/test_app/TestApp/webroot/img/image.jpg\' />'
        );
        $this->assertInstanceof('Cake\ORM\Entity', $result);
        $this->assertEquals(
            'https://raw.githubusercontent.com/mirko-pagliai/me-cms/master/tests/test_app/TestApp/webroot/img/image.jpg',
            $result->url
        );
        $this->assertEquals(400, $result->width);
        $this->assertEquals(300, $result->height);

        foreach (['image.jpg', WWW_ROOT . 'img' . DS . 'image.jpg'] as $image) {
            $result = $getPreviewMethod('<img src=\'' . $image . '\' />');
            $this->assertInstanceof('Cake\ORM\Entity', $result);
            $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+$/', $result->url);
            $this->assertEquals(400, $result->width);
            $this->assertEquals(300, $result->height);
        }

        $youtubeId = '6z4KK7RWjmk';
        $result = $getPreviewMethod('[youtube]' . $youtubeId . '[/youtube]');
        $this->assertInstanceof('Cake\ORM\Entity', $result);
        $this->assertEquals(Youtube::getPreview($youtubeId), $result->url);
        $this->assertEquals(400, $result->width);
        $this->assertEquals(300, $result->height);
    }
}
