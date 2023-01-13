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
 * @var array{platform: string, browser: string, version: string, agent: string, ip: string, time: \Cake\I18n\FrozenTime}[] $loginLog
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
                            <?= $log['time']->i18nFormat() ?>
                        </div>
                        <div class="d-lg-none">
                            <div><?= $log['time']->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                            <div><?= $log['time']->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                        </div>
                    </td>
                    <td class="text-center text-nowrap">
                        <?= $log['ip'] ?>
                        <small>(<?= $this->Html->link(
                            __d('me_cms', 'Who is'),
                            str_replace('{IP}', $log['ip'], getConfigOrFail('security.ip_whois')),
                            ['target' => '_blank']) ?>)</small>
                    </td>
                    <td class="text-center">
                        <samp>
                            <?= __d('me_cms', '{0} {1} on {2}', $log['browser'], $log['version'], $log['platform']) ?>
                        </samp>
                    </td>
                    <td class="small">
                        <samp><?= $log['agent'] ?></samp>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
