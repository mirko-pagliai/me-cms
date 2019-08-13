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

<?php if ($loginLog) : ?>
    <table class="table table-hover">
        <thead>
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
                    <td class="text-center text-nowrap">
                        <div class="d-none d-lg-block">
                            <?= $log->get('time')->i18nFormat() ?>
                        </div>
                        <div class="d-lg-none">
                            <div><?= $log->get('time')->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                            <div><?= $log->get('time')->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                        </div>
                    </td>
                    <td class="text-center text-nowrap">
                        <?= sprintf(
                            '%s (%s | %s)',
                            $log->get('ip'),
                            $this->Html->link(
                                __d('me_cms', 'Who is'),
                                str_replace('{IP}', $log->get('ip'), getConfigOrFail('security.ip_whois')),
                                ['target' => '_blank']
                            ),
                            $this->Html->link(
                                __d('me_cms', 'Map'),
                                str_replace('{IP}', $log->get('ip'), getConfigOrFail('security.ip_map')),
                                ['target' => '_blank']
                            )
                        ) ?>
                    </td>
                    <td class="text-center">
                        <samp>
                            <?= __d('me_cms', '{0} {1} on {2}', $log->get('browser'), $log->get('version'), $log->get('platform')) ?>
                        </samp>
                    </td>
                    <td class="small">
                        <samp><?= $log->get('agent') ?></samp>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
