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
 * @since       2.17.0
 */
namespace MeCms\Model\Table\Traits;

use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
use DOMDocument;
use MeTools\Utility\Youtube;
use Thumber\ThumbTrait;
use Thumber\Utility\ThumbCreator;

/**
 * This trait provides a method to get the first available image or the preview
 *  of the first YouTube video
 */
trait GetPreviewsFromTextTrait
{
    use ThumbTrait;

    /**
     * Internal method to get the first image from an html string
     * @param string $html Html string
     * @return string|bool Image or `false`
     * @since 2.20.0
     */
    protected function firstImage($html)
    {
        if (empty($html)) {
            return false;
        }

        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $item = $dom->getElementsByTagName('img')->item(0);

        if ($item) {
            $src = $item->getAttribute('src');

            if (in_array(strtolower(pathinfo($src, PATHINFO_EXTENSION)), ['gif', 'jpg', 'jpeg', 'png'])) {
                return $src;
            }
        }

        return false;
    }

    /**
     * Gets the first available image or the preview of the first YouTube video
     * @param string $text The text within which to search
     * @return Entity|null An `Entity` with `url`, `width` and `height`
     *  properties or `null` if there is not no preview
     * @uses firstImage()
     * @uses getPreviewSize()
     */
    public function getPreview($text)
    {
        $url = $this->firstImage($text);

        if ($url && !isUrl($url)) {
            //If is relative path
            if (!Folder::isAbsolute($url)) {
                $url = WWW_ROOT . 'img' . DS . $url;
            }

            if (!file_exists($url)) {
                return null;
            }

            $thumb = (new ThumbCreator($url))->resize(1200, 1200)->save(['format' => 'jpg']);
            $url = $this->getUrl($thumb, true);
        } elseif (preg_match('/\[youtube](.+?)\[\/youtube]/', $text, $matches)) {
            $url = Youtube::getPreview($matches[1]);
        }

        if (empty($url)) {
            return null;
        }

        list($width, $height) = $this->getPreviewSize($url);

        return new Entity(compact('url', 'width', 'height'));
    }

    /**
     * Internal method to get the preview size
     * @param string $image Image url or path
     * @return array Array with width and height
     */
    protected function getPreviewSize($image)
    {
        return array_slice(getimagesize($image), 0, 2);
    }
}
