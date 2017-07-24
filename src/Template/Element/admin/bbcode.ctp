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
?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <?= __d('me_cms', 'BBCode') ?>
                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                <table class="table margin-0">
                    <thead>
                        <tr>
                            <th><?= __d('me_cms', 'BBCode tag') ?></th>
                            <th><?= __d('me_cms', 'Description') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="min-width">
                                <code>[readmore /]</code>
                            </td>
                            <td>
                                <?= __d('me_cms', 'Manually indicates where to cut the text and show the "Read more" button') ?>.
                                <?= __d('me_cms', 'If this is not present, the system can still cut the text, for example after a certain number of characters') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="min-width">
                                <p><code>[img]mypic.gif[/img]</code></p>
                                <p><code>[img]http://example.com/mypic.gif[/img]</code></p>
                            </td>
                            <td>
                                <?= __d('me_cms', 'Adds an image') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="min-width">
                                <code>[url="http://example.com"]my link[/url]</code>
                            </td>
                            <td>
                                <?= __d('me_cms', 'Adds a link') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="min-width">
                                <p><code>[youtube]https://youtu.be/bL_CJKq9rIw[/youtube]</code></p>
                                <p><code>[youtube]bL_CJKq9rIw[/youtube]</code></p>
                            </td>
                            <td>
                                <?= __d('me_cms', 'Adds a {0} video. You may indicate the ID or the url of the video', 'YouTube') ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>