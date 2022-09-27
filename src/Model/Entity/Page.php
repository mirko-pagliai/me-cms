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

use Cake\Routing\Router;
use MeCms\ORM\PostAndPageEntity;

/**
 * Page entity
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $subtitle
 * @property string $slug
 * @property string $text
 * @property int $priority
 * @property bool $active
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \MeCms\Model\Entity\PagesCategory $category
 */
class Page extends PostAndPageEntity
{
    /**
     * Virtual fields that should be exposed
     * @var array<string>
     */
    protected $_virtual = ['plain_text', 'url'];

    /**
     * Gets the url (virtual field)
     * @return string|null
     * @since 2.27.2
     */
    protected function _getUrl(): ?string
    {
        return $this->hasValue('slug') ? Router::url(['_name' => 'page', $this->get('slug')], true) : null;
    }
}
