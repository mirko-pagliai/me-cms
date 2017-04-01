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
namespace MeCms\Model\Entity;

use Cake\ORM\Entity;
use Thumber\Utility\ThumbCreator;

/**
 * Photo entity
 * @property int $id
 * @property int $album_id
 * @property string $filename
 * @property string $size
 * @property string $description
 * @property bool $active
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \MeCms\Model\Entity\PhotosAlbum $album
 */
class Photo extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['path', 'preview', 'thumbnail'];

    /**
     * Gets the photo path (virtual field)
     * @return string|void
     */
    protected function _getPath()
    {
        if (empty($this->_properties['album_id']) || empty($this->_properties['filename'])) {
            return;
        }

        return PHOTOS . $this->_properties['album_id'] . DS . $this->_properties['filename'];
    }

    /**
     * Gets the photo preview (virtual field)
     * @return array|void Array with `preview`, `width` and `height` keys
     * @uses _getPath()
     */
    protected function _getPreview()
    {
        $preview = $this->_getPath();

        if (!$preview) {
            return;
        }

        $thumb = (new ThumbCreator($preview))->resize(1200)->save(['format' => 'jpg']);
        $preview = thumbUrl($thumb, true);

        list($width, $height) = getimagesize($thumb);

        return compact('preview', 'width', 'height');
    }

    /**
     * Gets the photo thumbnail (virtual field)
     * @return string|void Thumbnail path
     * @uses _getPath()
     */
    protected function _getThumbnail()
    {
        $preview = $this->_getPath();

        if (!$preview) {
            return;
        }

        $thumb = (new ThumbCreator($preview))->resize(1200)->save(['format' => 'jpg']);
        return thumbUrl($thumb, true);
    }
}
