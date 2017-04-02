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
use Thumber\Utility\ThumbCreator;

/**
 * This trait provides a method to get the first available image or the preview
 *  of the first YouTube video
 */
trait GetPreviewFromTextTrait
{
    /**
     * Gets the first available image or the preview of the first YouTube video
     * @param string $text The text within which to search
     * @return array|null Array with `preview`, `width` and `height`
     *  properties or `null` if there is not no preview
     * @uses getPreviewSize()
     */
    public function getPreview($text)
    {
        $preview = firstImage($text);

        if ($preview && !isUrl($preview)) {
            //If is relative path
            if (!Folder::isAbsolute($preview)) {
                $preview = WWW_ROOT . 'img' . DS . $preview;
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
