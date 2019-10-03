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
namespace MeCms\Model\Entity;

use Cake\ORM\Entity;
use Cake\Routing\Router;
use MeTools\Utility\BBCode;
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
    protected $_virtual = ['path', 'plain_description', 'preview', 'url'];

    /**
     * Gets the photo path (virtual field)
     * @return string|null
     */
    protected function _getPath()
    {
        if (!$this->has('album_id') || !$this->has('filename')) {
            return null;
        }

        return PHOTOS . $this->get('album_id') . DS . $this->get('filename');
    }

    /**
     * Gets description as plain text (virtual field)
     * @return string|null
     */
    protected function _getPlainDescription()
    {
        return $this->has('description') ? trim(strip_tags((new BBCode())->remove($this->get('description')))) : null;
    }

    /**
     * Gets the photo preview (virtual field)
     * @return \Cake\ORM\Entity|null Entity with `preview`, `width` and `height`
     *  properties
     * @uses _getPath()
     */
    protected function _getPreview()
    {
        $path = $this->_getPath();

        if (!$path) {
            return null;
        }

        list($width, $height) = getimagesize($path);

        $thumber = new ThumbCreator($path);
        $thumber->resize(1200, 1200)->save(['format' => 'jpg']);

        return new Entity(['url' => $thumber->getUrl()] + compact('width', 'height'));
    }

    /**
     * Gets the url (virtual field)
     * @return string|null
     * @since 2.27.2
     */
    protected function _getUrl()
    {
        if (!$this->has('id') || !$this->get('album')->has('slug')) {
            return null;
        }

        return Router::url(['_name' => 'photo', 'slug' => $this->get('album')->get('slug'), 'id' => $this->get('id')], true);
    }
}
