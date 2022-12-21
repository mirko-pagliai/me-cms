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
 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\PagesCategory> $categories
 * @var \MeCms\View\View\AppView $this
 */

use MeCms\Model\Entity\PagesCategory;

if (empty($categories) || $categories->count() < 2) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __d('me_cms', 'Pages categories'));

$categories = $categories->map(fn(PagesCategory $category): string => $this->Html->link($category->get('title'), ['_name' => 'pagesCategory', $category->get('slug')]))->toArray();

echo $this->Html->ul($categories, ['icon' => 'caret-right']);
