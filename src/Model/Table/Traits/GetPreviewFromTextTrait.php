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
 * @since       2.17.0
 */
namespace MeCms\Model\Table\Traits;

use Cake\Filesystem\Folder;
use MeTools\Utility\Youtube;
use Sunra\PhpSimple\HtmlDomParser;
use Thumber\Utility\ThumbCreator;

/**
 * This trait provides a method to get the first available image or the preview
 *  of the first YouTube video
 */
trait GetPreviewFromTextTrait
{
    /**
     * Internal method to get the first image from an html string
     * @param string $html Html string
     * @return string|bool Image or `false`
     * @since 2.19.3
     */
    protected function firstImage($html)
    {
        $dom = HtmlDomParser::str_get_html($html);

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
            $preview = thumbUrl($thumb, true);
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
