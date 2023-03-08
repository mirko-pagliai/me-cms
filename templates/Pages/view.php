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

/**
 * @var \MeCms\Model\Entity\Page $page
 * @var \MeCms\View\View\AppView $this
 */
$this->extend('/common/view');
$this->assign('title', $page->get('title'));

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
if ($this->getRequest()->is('action', 'view', 'Pages')) {
    $this->Html->meta(['content' => 'article', 'property' => 'og:type']);

    if ($page->hasValue('modified')) {
        $this->Html->meta(['content' => $page->get('modified')->toUnixString(), 'property' => 'og:updated_time']);
    }

    if ($page->hasValue('preview')) {
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
