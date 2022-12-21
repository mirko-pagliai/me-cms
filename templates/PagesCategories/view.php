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

 * @var \MeCms\Model\Entity\PagesCategory $category
 * @var \MeCms\View\View\AppView $this
 */

use MeCms\Model\Entity\Page;

$this->extend('/common/index');
$this->assign('title', $category->get('title'));

/**
 * Userbar
 */
$this->addToUserbar($this->Html->link(
    __d('me_cms', 'Edit category'),
    ['action' => 'edit', $category->get('id'), 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link', 'icon' => 'pencil-alt', 'target' => '_blank']
));
$this->addToUserbar($this->Form->postLink(
    __d('me_cms', 'Delete category'),
    ['action' => 'delete', $category->get('id'), 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link text-danger', 'icon' => 'trash-alt', 'confirm' => I18N_SURE_TO_DELETE, 'target' => '_blank']
));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($category->get('title'), ['_name' => 'pagesCategory', $category->get('title')]);

$pages = collection($category->get('pages'))
    ->map(fn(Page $page): string => $this->Html->link($page->get('title'), $page->get('url')))
    ->toList();

echo $this->Html->ul($pages, ['icon' => 'caret-right']);
