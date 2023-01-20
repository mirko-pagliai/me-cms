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
 *
 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\Page> $pages
 * @var \MeCms\View\View\AppView $this
 */

use MeCms\Model\Entity\Page;

if (empty($pages) || $pages->isEmpty()) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', I18N_PAGES);

$pages = $pages->map(fn(Page $page): string => $this->Html->link($page->get('title'), ['_name' => 'page', $page->get('slug')]))->toArray();

echo $this->Html->ul($pages, ['icon' => 'caret-right']);
