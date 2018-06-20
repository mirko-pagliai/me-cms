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
use Cake\View\HelperRegistry;
use Cake\View\View;

/**
 * Post entity
 * @property int $id
 * @property int $category_id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $subtitle
 * @property string $text
 * @property string $preview
 * @property int $priority
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property bool $active
 * @property \MeCms\Model\Entity\PostsCategory $category
 * @property \MeCms\Model\Entity\User $user
 * @property \MeCms\Model\Entity\Tag[] $tags
 */
class Post extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'preview' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['plain_text', 'tags_as_string'];

    /**
     * Gets text as plain text (virtual field)
     * @return string|void
     */
    protected function _getPlainText()
    {
        if (empty($this->_properties['text'])) {
            return;
        }

        //Loads the `BBCode` helper
        $BBCode = (new HelperRegistry(new View))->load(ME_TOOLS . '.BBCode');

        return trim(strip_tags($BBCode->remove($this->_properties['text'])));
    }

    /**
     * Gets tags as string, separated by a comma and a space (virtual field)
     * @return string|void
     */
    protected function _getTagsAsString()
    {
        if (empty($this->_properties['tags'])) {
            return;
        }

        return implode(', ', collection($this->_properties['tags'])->extract('tag')->toList());
    }
}
