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

use Cake\Collection\CollectionInterface;
use Cake\ORM\Entity;
use MeCms\TestSuite\TestCase;
use MeTools\Utility\Youtube;

/**
 * GetPreviewsFromTextTraitTest class
 */
class GetPreviewsFromTextTraitTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $Posts;

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
     * Test for `extractImages()` method
     * @test
     */
    public function testExtractImages(): void
    {
        $extractImagesMethod = function (string $value) {
            return $this->invokeMethod($this->Posts, 'extractImages', [$value]);
        };

        foreach ([
            '',
            'Text',
            '<img src=\'\'>',
            '<img src=\'a\'>',
            '<img src=\'a.a\'>',
            '<img src=\'data:\'>',
            '<img src=\'text.txt\'>',
        ] as $value) {
            $this->assertEmpty($extractImagesMethod($value));
        }

        //Values with some attributes
        foreach ([
            '<img alt=\'\' src=\'image.jpg\'>',
            '<img alt="" src="image.jpg">',
            '<img alt=\'\' class=\'my-class\' src=\'image.jpg\'>',
            '<img alt="" class="my-class" src="image.jpg">',
        ] as $value) {
            $this->assertEquals(['image.jpg'], $extractImagesMethod($value));
        }

        //Remote images
        foreach ([
            '<img src=\'http://example.com/image.jpg\'>',
            '<img src=\'http://example.com/image.jpg\' />',
            '<img src=\'http://example.com/image.jpg\' />Text',
            '<img src=\'http://example.com/image.jpg\' /> Text',
        ] as $value) {
            $this->assertEquals(['http://example.com/image.jpg'], $extractImagesMethod($value));
        }

        //Different protocols
        foreach ([
            'ftp://example.com/image.jpg',
            'https://example.com/image.jpg',
            'http://www.example.com/image.jpg',
        ] as $value) {
            $this->assertEquals([$value], $extractImagesMethod('<img src=\'' . $value . '\'>'));
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
            $this->assertEquals([$filename], $extractImagesMethod('<img src=\'' . $filename . '\'>'));
        }

        //Two images
        foreach ([
            '<img src=\'image.jpg\' /><img src=\'image.gif\' />',
            '<img src=\'image.jpg\'><img src=\'image.gif\'>',
            '<img src=\'image.jpg\'> Text <img src=\'image.gif\'>',
        ] as $value) {
            $this->assertEquals(['image.jpg', 'image.gif'], $extractImagesMethod($value));
        }

        //Youtube video
        $youtubeId = '6z4KK7RWjmk';
        $this->assertEquals([Youtube::getPreview($youtubeId)], $extractImagesMethod('[youtube]' . $youtubeId . '[/youtube]'));

        //Image and Youtube video
        $expected = ['http://example.com/image.jpg', Youtube::getPreview($youtubeId)];
        $result = $extractImagesMethod('[youtube]' . $youtubeId . '[/youtube]<img src=\'http://example.com/image.jpg\'>');
        $this->assertEquals($expected, $result);

        //Two Youtube videos
        $youtubeId = ['6z4KK7RWjmk', '6z4KK7RWjmj'];
        $expected = [Youtube::getPreview($youtubeId[0]), Youtube::getPreview($youtubeId[1])];
        $result = $extractImagesMethod('[youtube]' . $youtubeId[0] . '[/youtube][youtube]' . $youtubeId[1] . '[/youtube]');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for `getPreviewSize()` method
     * @test
     */
    public function testGetPreviewSize(): void
    {
        $result = $this->invokeMethod($this->Posts, 'getPreviewSize', [WWW_ROOT . 'img' . DS . 'image.jpg']);
        $this->assertEquals([400, 400], $result);
    }

    /**
     * Test for `getPreviews()` method
     * @test
     */
    public function testGetPreviews(): void
    {
        /** @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject $Posts */
        $Posts = $this->getMockForModel('MeCms.Posts', ['getPreviewSize']);
        $Posts->method('getPreviewSize')->will($this->returnValue([400, 300]));

        foreach ([
            '',
            'Text',
            '<img src=\'\'>',
            '<img src=\'a\'>',
            '<img src=\'a.a\'>',
            '<img src=\'data:\'>',
            '<img src=\'text.txt\'>',
        ] as $value) {
            $this->assertTrue($Posts->getPreviews($value)->isEmpty());
        }

        //No existing file
        $result = $Posts->getPreviews('<img src=\'' . WWW_ROOT . 'img' . DS . 'noExisting.jpg\' />');
        $this->assertTrue($result->isEmpty());

        $result = $Posts->getPreviews('<img src=\'http://example.com/image.jpg\' />');
        $this->assertInstanceOf(CollectionInterface::class, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Entity::class, $result);
        $this->assertEquals('http://example.com/image.jpg', $result->first()->get('url'));
        $this->assertEquals(400, $result->first()->get('width'));
        $this->assertEquals(300, $result->first()->get('height'));

        foreach (['image.jpg', WWW_ROOT . 'img' . DS . 'image.jpg'] as $image) {
            $result = $Posts->getPreviews('<img src=\'' . $image . '\' />');
            $this->assertCount(1, $result);
            $this->assertMatchesRegularExpression('/^http:\/\/localhost\/thumb\/[A-z0-9]+$/', $result->first()->get('url'));
            $this->assertEquals(400, $result->first()->get('width'));
            $this->assertEquals(300, $result->first()->get('height'));
        }

        $youtubeId = '6z4KK7RWjmk';
        $result = $Posts->getPreviews('[youtube]' . $youtubeId . '[/youtube]');
        $this->assertCount(1, $result);
        $this->assertEquals(Youtube::getPreview($youtubeId), $result->first()->get('url'));
        $this->assertEquals(400, $result->first()->get('width'));
        $this->assertEquals(300, $result->first()->get('height'));
    }
}
