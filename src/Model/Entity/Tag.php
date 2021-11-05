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
use Cake\Utility\Text;
use Tools\Exceptionist;

/**
 * Tag entity
 * @property int $id
 * @property string $tag
 * @property int $post_count
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \MeCms\Model\Entity\Post[] $posts
 */
class Tag extends Entity
{
    /**
     * Fields that can be mass assigned
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'post_count' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['slug', 'url'];

    /**
     * Gets the tag slug (virtual field)
     * @return string
     * @throws \Tools\Exception\PropertyNotExistsException
     */
    protected function _getSlug(): ?string
    {
        Exceptionist::objectPropertyExists($this, 'tag');

        return strtolower(Text::slug($this->get('tag')));
    }

    /**
     * Gets the url (virtual field)
     * @return string
     * @since 2.27.2
     * @throws \Tools\Exception\PropertyNotExistsException
     */
    protected function _getUrl(): string
    {
        Exceptionist::objectPropertyExists($this, 'slug');

        return Router::url(['_name' => 'postsTag', $this->get('slug')], true);
    }
}
