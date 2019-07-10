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
namespace MeCms\Model\Entity;

use Cake\ORM\Entity;

/**
 * PhotosAlbum entity
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property int $photo_count
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class PhotosAlbum extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'photo_count' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['path', 'preview'];

    /**
     * Gets the album full path (virtual field)
     * @return string|null
     */
    protected function _getPath(): ?string
    {
        return empty($this->_properties['id']) ? null : PHOTOS . $this->_properties['id'];
    }

    /**
     * Gets the album preview (virtual field)
     * @return string|null
     * @since 2.21.1
     */
    protected function _getPreview(): ?string
    {
        if (empty($this->_properties['photos'])) {
            return null;
        }

        return collection($this->_properties['photos'])->extract('path')->first();
    }
}
