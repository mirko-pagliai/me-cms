<?php
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
$this->extend('/Posts/index');
$this->assign('title', $title = __d('me_cms', 'Tag {0}', $tag->tag));

/**
 * Userbar
 */
$this->userbar($this->Html->link(__d('me_cms', 'Edit tag'), [
    'controller' => 'PostsTags',
    'action' => 'edit',
    'prefix' => ADMIN_PREFIX,
    $tag->id,
], ['icon' => 'pencil', 'target' => '_blank']));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(__d('me_cms', 'Tags'), ['_name' => 'postsTags']);
$this->Breadcrumbs->add($title, ['_name' => 'postsTag', $tag->slug]);
