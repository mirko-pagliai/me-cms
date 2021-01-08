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
 */
$this->extend('/Admin/common/view');
$this->assign('title', __d('me_cms', 'Log {0}', $filename));

$this->append('actions', $this->Html->button(
    I18N_DOWNLOAD,
    ['action' => 'download', $filename],
    ['class' => 'btn-success', 'icon' => 'download']
));
$this->append('actions', $this->Form->postButton(
    I18N_DELETE,
    ['action' => 'delete', $filename],
    ['class' => 'btn-danger', 'icon' => 'trash-alt', 'confirm' => I18N_SURE_TO_DELETE]
));
?>

<?php if (!empty($content)) : ?>
    <?php foreach ($content as $k => $row) : ?>
        <?php
        if (in_array($row->get('level'), ['error', 'fatal'])) {
            $class = 'bg-danger';
        } elseif (in_array($row->get('level'), ['warning', 'notice'])) {
            $class = 'bg-warning';
        } else {
            $class = 'bg-info';
        }
        $class .= ' text-white p-2';
        ?>

        <div class="<?= $class ?> mb-1 p-1">
            <strong><?= $row->get('datetime') ?> - <?= $row->get('message') ?></strong>
        </div>

        <?php if (!empty($row->get('request')) || !empty($row->get('referer')) || !empty($row->get('ip'))) : ?>
            <div class="mb-1 text-muted">
                <?php if (!empty($row->get('request'))) : ?>
                    <div class="small text-truncated">
                        <?= __d('me_cms', 'Request URL') ?>:
                        <?= $this->Html->link(
                            $row->get('request') === '/' ? '(Root)' : $row->get('request'),
                            $row->get('request'),
                            ['target' => '_blank']
                        ) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($row->get('referer'))) : ?>
                    <div class="small text-truncated">
                        <?= __d('me_cms', 'Referer URL') ?>:
                        <?= $this->Html->link($row->get('referer'), $row->get('referer'), ['target' => '_blank']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($row->get('ip'))) : ?>
                    <div>
                        <?= sprintf(
                            '%s: %s (%s | %s)',
                            __d('me_cms', 'Client IP'),
                            $row->get('ip'),
                            $this->Html->link(
                                __d('me_cms', 'Who is'),
                                str_replace('{IP}', $row->get('ip'), getConfigOrFail('security.ip_whois')),
                                ['target' => '_blank']
                            ),
                            $this->Html->link(
                                __d('me_cms', 'Map'),
                                str_replace('{IP}', $row->get('ip'), getConfigOrFail('security.ip_map')),
                                ['target' => '_blank']
                            )
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        $buttons = $collapse = [];

        if (!empty($row->get('attributes'))) {
            $buttons[] = $this->Html->button(__d('me_cms', 'Exception attributes'), "#log-attributes-{$k}", [
                'class' => 'btn-sm btn-primary',
                'data-toggle' => 'collapse',
                'data-target' => "#log-attributes-{$k}",
            ]);
            $collapse[] = $this->Html->div(
                'collapse',
                $this->Html->pre($row->get('attributes'), ['class' => 'pre-scrollable mb-2']),
                ['id' => "log-attributes-{$k}"]
            );
        }

        if (!empty($row->get('trace'))) {
            $buttons[] = $this->Html->button(__d('me_cms', 'Trace'), "#log-trace-{$k}", [
                'class' => 'btn-sm btn-primary',
                'data-toggle' => 'collapse',
                'data-target' => "#log-trace-{$k}",
            ]);
            $collapse[] = $this->Html->div(
                'collapse',
                $this->Html->pre($row->get('trace'), ['class' => 'pre-scrollable mb-2']),
                ['id' => "log-trace-{$k}"]
            );
        }

        $buttons[] = $this->Html->button(__d('me_cms', 'Full log'), "#log-full-{$k}", [
            'class' => 'btn-sm btn-primary',
            'data-toggle' => 'collapse',
            'data-target' => "#log-full-{$k}",
        ]);
        $collapse[] = $this->Html->div(
            'collapse',
            $this->Html->pre($row->get('full'), ['class' => 'pre-scrollable mb-2']),
            ['id' => "log-full-{$k}"]
        );

        echo $this->Html->div('btn-group mb-3', implode(PHP_EOL, $buttons), ['role' => 'group']);
        echo implode(PHP_EOL, $collapse);
        ?>
    <?php endforeach; ?>
<?php endif; ?>
