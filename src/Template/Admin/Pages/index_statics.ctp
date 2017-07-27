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
$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Static pages'));
?>

<table class="table table-striped">
    <tr>
        <th><?= __d('me_cms', 'Filename') ?></th>
        <th class="text-center"><?= __d('me_cms', 'Title') ?></th>
        <th><?= __d('me_cms', 'Path') ?></th>
    </tr>
    <?php foreach ($pages as $page) : ?>
        <tr>
            <td>
                <strong>
                    <?= $this->Html->link($page->filename, ['_name' => 'page', $page->slug], ['target' => '_blank']) ?>
                </strong>
                <?php
                $actions = [
                    $this->Html->link(
                        __d('me_cms', 'Open'),
                        ['_name' => 'page', $page->slug],
                        ['icon' => 'external-link', 'target' => '_blank']
                    ),
                ];

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </td>
            <td class="text-center">
                <?= $page->title ?>
            </td>
            <td>
                <samp><?= $page->path ?></samp>
            </td>
        </tr>
    <?php endforeach; ?>
</table>