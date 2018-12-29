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
 * Page entity
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $subtitle
 * @property string $slug
 * @property string $text
 * @property int $priority
 * @property bool $active
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \MeCms\Model\Entity\PagesCategory $category
 */
class Page extends Entity
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
    protected $_virtual = ['plain_text'];

    /**
     * Gets text as plain text (virtual field)
     * @return string|null
     */
    protected function _getPlainText()
    {
        if (empty($this->_properties['text'])) {
            return null;
        }

        //Loads the `BBCode` helper
        $BBCode = (new HelperRegistry(new View))->load('MeTools.BBCode');

        return trim(strip_tags($BBCode->remove($this->_properties['text'])));
    }
}
