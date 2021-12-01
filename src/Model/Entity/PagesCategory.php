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
use Cake\Routing\Router;

/**
 * PagesCategory entity
 * @property int $id
 * @property int $parent_id
 * @property int $lft
 * @property int $rght
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property int $page_count
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \MeCms\Model\Entity\PagesCategory[] $child_pages_categories
 * @property \MeCms\Model\Entity\PagesCategory $parent_pages_category
 */
class PagesCategory extends Entity
{
    /**
     * Fields that can be mass assigned
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'page_count' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['url'];

    /**
     * Gets the url (virtual field)
     * @return string|null
     * @since 2.27.2
     */
    protected function _getUrl(): ?string
    {
        return $this->hasValue('slug') ? Router::url(['_name' => 'pagesCategory', $this->get('slug')], true) : null;
    }
}
