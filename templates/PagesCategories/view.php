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
use MeCms\Model\Entity\Page;

/**
 * @var \MeCms\Model\Entity\PagesCategory $category
 * @var \MeCms\View\View\AppView $this
 */

$this->extend('/common/index');
$this->assign('title', $category->get('title'));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($category->get('title'), ['_name' => 'pagesCategory', $category->get('title')]);

$pagesLinks = array_map(fn(Page $page): string => $this->Html->link($page->get('title'), $page->get('url')), $category->get('pages'));

echo $this->Html->ul($pagesLinks, ['icon' => 'caret-right']);
