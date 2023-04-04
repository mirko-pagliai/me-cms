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
 * @var \MeCms\Model\Entity\Page[] $pages
 * @var \MeCms\View\View\Admin\AppView $this
 */

$this->extend('MeCms./Admin/common/index');
$this->assign('title', __d('me_cms', 'Static pages'));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th><?= I18N_FILENAME ?></th>
            <th class="text-center"><?= I18N_TITLE ?></th>
            <th class="text-nowrap"><?= __d('me_cms', 'Path') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page) : ?>
            <tr>
                <td>
                    <?php
                    echo $this->Html->link($page->get('filename'), ['_name' => 'page', $page->get('slug')], ['class' => 'fw-bold', 'target' => '_blank']);

                    echo $this->Html->ul([
                        $this->Html->link(I18N_OPEN, ['_name' => 'page', $page->get('slug')], ['icon' => 'external-link-alt', 'target' => '_blank']),
                    ], ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center align-middle">
                    <?= $page->get('title') ?>
                </td>
                <td class="text-nowrap align-middle">
                    <code><?= $page->get('path') ?></code>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
