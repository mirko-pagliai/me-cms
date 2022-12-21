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

 * @var \MeCms\View\View\AppView $this
 */

use MeCms\Model\Entity\Tag;

$this->extend('/common/index');
$this->assign('title', $title = __d('me_cms', 'Posts tags'));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title, ['_name' => 'postsTags']);

$tags = $tags->map(fn(Tag $tag): string => $this->Html->link($tag->get('tag'), $tag->get('url')))->toList();

echo $this->Html->ul($tags, ['icon' => 'caret-right']);

echo $this->element('MeTools.paginator');
