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
 * @var \MeCms\Model\Entity\Tag $tag
 * @var \MeCms\View\View\AppView $this
 */

$this->extend('/Posts/index');
$this->assign('title', $title = __d('me_cms', 'Tag {0}', $tag->get('tag')));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(I18N_TAGS, ['_name' => 'postsTags']);
$this->Breadcrumbs->add($title, ['_name' => 'postsTag', $tag->get('slug')]);
