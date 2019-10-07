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
$this->extend('/Common/view');
$this->assign('title', $page->get('title'));

/**
 * Userbar
 */
$class = 'badge badge-warning';
if (!$page->get('active')) {
    $this->userbar($this->Html->span(I18N_DRAFT, compact('class')));
}
if ($page->get('created')->isFuture()) {
    $this->userbar($this->Html->span(I18N_SCHEDULED, compact('class')));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit page'),
    ['action' => 'edit', $page->get('id'), 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link', 'icon' => 'pencil-alt', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete page'),
    ['action' => 'delete', $page->get('id'), 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link text-danger', 'icon' => 'trash-alt', 'confirm' => I18N_SURE_TO_DELETE, 'target' => '_blank']
));

/**
 * Breadcrumb
 */
if (getConfig('page.category')) {
    $this->Breadcrumbs->add($page->get('category')->get('title'), $page->get('category')->get('url'));
}
$this->Breadcrumbs->add($page->get('title'), $page->get('url'));

/**
 * Meta tags
 */
if ($this->getRequest()->isAction('view', 'Pages')) {
    $this->Html->meta(['content' => 'article', 'property' => 'og:type']);

    if ($page->has('modified')) {
        $this->Html->meta(['content' => $page->get('modified')->toUnixString(), 'property' => 'og:updated_time']);
    }

    if ($page->has('preview')) {
        foreach ($page->get('preview') as $preview) {
            $this->Html->meta(['href' => $preview->get('url'), 'rel' => 'image_src']);
            $this->Html->meta(['content' => $preview->get('url'), 'property' => 'og:image']);
            $this->Html->meta(['content' => $preview->get('width'), 'property' => 'og:image:width']);
            $this->Html->meta(['content' => $preview->get('height'), 'property' => 'og:image:height']);
        }
    }

    $this->Html->meta([
        'content' => $this->Text->truncate($page->get('plain_text'), 100, ['html' => true]),
        'property' => 'og:description',
    ]);
}

echo $this->element('views/page', compact('page'));
