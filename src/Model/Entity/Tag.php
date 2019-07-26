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
use Cake\Utility\Text;

/**
 * Tag entity
 * @property int $id
 * @property string $tag
 * @property int $post_count
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \MeCms\Model\Entity\Post[] $posts
 */
class Tag extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity([]) or patchEntity()
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
    protected $_virtual = ['slug'];

    /**
     * Gets the tag slug (virtual field)
     * @return string|null
     */
    protected function _getSlug(): ?string
    {
        return $this->has('tag') ? strtolower(Text::slug($this->get('tag'))) : null;
    }
}
