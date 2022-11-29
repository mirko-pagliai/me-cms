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
 * @since       2.30.0
 */

namespace MeCms\View\Helper;

/**
 * Topbar Helper.
 *
 * This helper returns an array with the links to put in the topbar.
 */
class TopbarHelper extends AbstractTopbarHelper
{
    /**
     * Returns an array with the links to put in the topbar
     * @return array<string>
     */
    public function build(): array
    {
        return [
            $this->Html->link(__d('me_cms', 'Home'), ['_name' => 'homepage'], ['class' => 'nav-link']),
            $this->Html->link(__d('me_cms', 'Categories'), ['_name' => 'postsCategories'], ['class' => 'nav-link']),
            $this->Html->link(I18N_PAGES, ['_name' => 'pagesCategories'], ['class' => 'nav-link']),
        ];
    }
}
