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

/**
 * Banner entity
 * @property int $id
 * @property int $position_id
 * @property string $filename
 * @property string $target
 * @property string $description
 * @property bool $active
 * @property bool $thumbnail
 * @property int $click_count
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \MeCms\Model\Entity\Position $position
 */
class Banner extends Entity
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
    protected $_virtual = ['path', 'www'];

    /**
     * Gets the banner full path (virtual field)
     * @return string
     */
    protected function _getPath()
    {
        return BANNERS . $this->_properties['filename'];
    }

    /**
     * Gets the banner web address (virtual field)
     * @return string
     */
    protected function _getWww()
    {
        return BANNERS_WWW . $this->_properties['filename'];
    }
}
