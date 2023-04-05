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
 * @var \MeCms\View\View\Admin\AppView $this
 */
?>

<div class="border border-light rounded mb-3">
    <?= $this->Html->link(__d('me_cms', 'BBCode'), '#collapseBBCode', [
        'class' => 'h6 m-0 text-dark bg-light p-3 d-block rounded-top',
        'aria-controls' => 'collapseBBCode',
        'aria-expanded' => 'false',
        'data-bs-toggle' => 'collapse',
    ]) ?>
    <div class="collapse" id="collapseBBCode">
        <table class="table table-sm m-0">
            <tbody>
                <tr>
                    <td class="text-nowrap">
                        <code>[hr /]</code>
                    </td>
                    <td>
                        <?= __d('me_cms', 'Creates a horizontal line') ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-nowrap">
                        <code>[readmore /]</code>
                    </td>
                    <td>
                        <?= __d('me_cms', 'Manually indicates where to cut the text and show the "Read more" button') ?>.
                        <?= __d('me_cms', 'If this is not present, the system can still cut the text, for example after a certain number of characters') ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-nowrap">
                        <code>[img]mypic.gif[/img]</code>
                        <br />
                        <code>[img]http://example.com/mypic.gif[/img]</code>
                    </td>
                    <td>
                        <?= __d('me_cms', 'Adds an image') ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-nowrap">
                        <code>[url="http://example.com"]my link[/url]</code>
                    </td>
                    <td>
                        <?= __d('me_cms', 'Adds a link') ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-nowrap">
                        <code>[youtube]https://youtu.be/bL_CJKq9rIw[/youtube]</code>
                        <br />
                        <code>[youtube]bL_CJKq9rIw[/youtube]</code>
                    </td>
                    <td>
                        <?= __d('me_cms', 'Adds a {0} video. You may indicate the ID or the url of the video', 'YouTube') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
