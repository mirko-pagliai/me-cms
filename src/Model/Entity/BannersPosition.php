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
 * BannersPosition entity
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $banner_count
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class BannersPosition extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity([]) or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'banner_count' => false,
        'modified' => false,
    ];
}
