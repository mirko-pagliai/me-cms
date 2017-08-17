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

<?php if (!empty($loginLog)) : ?>
    <table class="table table-hover">
        <thead class="thead-default">
            <tr>
                <th class="text-center"><?= __d('me_cms', 'Time') ?></th>
                <th class="text-center"><?= __d('me_cms', 'IP') ?></th>
                <th class="text-center"><?= __d('me_cms', 'Browser') ?></th>
                <th><?= __d('me_cms', 'Client') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loginLog as $log) : ?>
                <tr>
                    <td class="text-center">
                        <?= $log->time->i18nFormat(getConfigOrFail('main.datetime.long')) ?>
                    </td>
                    <td class="text-center">
                        <?= $log->ip ?>
                        <?= sprintf(
                            '(%s | %s)',
                            $this->Html->link(
                                __d('me_cms', 'Who is'),
                                str_replace('{IP}', $log->ip, getConfigOrFail('security.ip_whois')),
                                ['target' => '_blank']
                            ),
                            $this->Html->link(
                                __d('me_cms', 'Map'),
                                str_replace('{IP}', $log->ip, getConfigOrFail('security.ip_map')),
                                ['target' => '_blank']
                            )
                        ) ?>
                    </td>
                    <td class="text-center">
                        <samp><?= __d('me_cms', '{0} {1} on {2}', $log->browser, $log->version, $log->platform) ?></samp>
                    </td>
                    <td>
                        <samp><?= $log->agent ?></samp>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>