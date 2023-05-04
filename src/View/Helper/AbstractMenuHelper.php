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

namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * AbstractMenuHelper.
 *
 * Other "menu helpers" have to extend this abstract class and implement its missing methods.
 * @property \MeCms\View\Helper\IdentityHelper $Identity
 */
abstract class AbstractMenuHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = ['MeCms.Identity'];

    /**
     * @var string
     */
    protected string $name;

    /**
     * Gets the links for this menu. Each links is an array of parameters
     * @return array[]
     */
    abstract public function getLinks(): array;

    /**
     * Gets the name of the current plugin
     * @return string
     * @throws \ReflectionException
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            $plugin = substr(get_class($this), 0, strpos(get_class($this), '\View\Helper') ?: 0);

            $this->name = strtolower(str_replace('\\', '-', $plugin) . '-' . substr(get_class_short_name($this), 0, -10));
        }

        return $this->name;
    }

    /**
     * Gets the options for this menu
     * @return array
     */
    abstract public function getOptions(): array;

    /**
     * Gets the title for this menu
     * @return string
     */
    abstract public function getTitle(): string;
}
