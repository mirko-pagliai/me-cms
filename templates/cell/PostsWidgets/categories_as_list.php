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

use MeCms\Model\Entity\PostsCategory;

if (empty($categories) || $categories->count() < 2) {
    return;
}

$this->extend('/common/widget');
$this->assign('title', I18N_POSTS_CATEGORIES);

$categories = $categories->map(function (PostsCategory $category) {
    return $this->Html->link($category->get('title'), ['_name' => 'postsCategory', $category->get('slug')]);
})->toArray();

echo $this->Html->ul($categories, ['icon' => 'caret-right']);
