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
 * @since       2.26.5
 */

namespace MeCms\ORM;

use Cake\ORM\Entity;
use MeTools\Utility\BBCode;
use Tools\Exceptionist;

/**
 * Abstract class for `Post` and `Page` entity classes.
 *
 * This class provides some methods and properties common to both classes.
 */
abstract class PostAndPageEntity extends Entity
{
    /**
     * Fields that can be mass assigned
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'preview' => false,
        'modified' => false,
    ];

    /**
     * Gets text as plain text (virtual field)
     * @return string
     * @throws \Tools\Exception\PropertyNotExistsException
     */
    protected function _getPlainText(): string
    {
        Exceptionist::objectPropertyExists($this, 'text');

        return trim(strip_tags($this->get('text')));
    }

    /**
     * Gets text
     * @param string|null $text Text
     * @return string
     * @since 2.27.2
     */
    protected function _getText(?string $text): string
    {
        return $text ? (new BBCode())->parser($text) : '';
    }

    /**
     * Gets the url (virtual field)
     * @return string
     * @since 2.27.2
     */
    abstract protected function _getUrl(): string;
}
