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
use MeTools\Utility\Youtube;
use Sunra\PhpSimple\HtmlDomParser;
use Thumber\ThumbTrait;
use Thumber\Utility\ThumbCreator;

/**
 * This trait provides a method to get the first available image or the preview
 *  of the first YouTube video
 */
trait GetPreviewFromTextTrait
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
        $dom = (new HtmlDomParser)->str_get_html($html);

        if (!$dom) {
            return false;
        }

        $img = $dom->find('img', 0);

        if (empty($img->src) ||
            !in_array(strtolower(pathinfo($img->src, PATHINFO_EXTENSION)), ['gif', 'jpg', 'jpeg', 'png'])
        ) {
            return false;
        }

        return $img->src;
    }

    /**
     * Gets the first available image or the preview of the first YouTube video
     * @param string $text The text within which to search
     * @return array|null Array with `preview`, `width` and `height`
     *  properties or `null` if there is not no preview
     * @uses firstImage()
     * @uses getPreviewSize()
     */
    public function getPreview($text)
    {
        $preview = $this->firstImage($text);

        if ($preview && !isUrl($preview)) {
            //If is relative path
            if (!Folder::isAbsolute($preview)) {
                $preview = WWW_ROOT . 'img' . DS . $preview;
            }

            if (!file_exists($preview)) {
                return null;
            }

            $thumb = (new ThumbCreator($preview))->resize(1200, 1200)->save(['format' => 'jpg']);
            $preview = $this->getUrl($thumb, true);
        } elseif (preg_match('/\[youtube](.+?)\[\/youtube]/', $text, $matches)) {
            $preview = Youtube::getPreview($matches[1]);
        }

        if (empty($preview)) {
            return null;
        }

        list($width, $height) = $this->getPreviewSize($preview);

        return compact('preview', 'width', 'height');
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
