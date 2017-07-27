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
$this->extend('/Admin/Common/view');
$this->assign('title', __d('me_cms', 'Log {0}', $filename));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Download'),
    ['action' => 'download', $filename],
    ['class' => 'btn-success', 'icon' => 'download']
));
$this->append('actions', $this->Form->postButton(
    __d('me_cms', 'Delete'),
    ['action' => 'delete', $filename],
    [
        'class' => 'btn-danger',
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
    ]
));
?>

<?php if (!empty($content)) : ?>
    <div class="as-table">
        <?php foreach ($content as $k => $row) : ?>
            <div class="padding-10 small">
                <?php
                if (in_array($row->level, ['error', 'fatal'])) {
                    $class = 'bg-danger text-danger';
                } elseif (in_array($row->level, ['warning', 'notice'])) {
                    $class = 'bg-warning text-warning';
                } else {
                    $class = 'bg-info text-info';
                }
                ?>

                <div class="<?= $class ?> margin-10 padding-10">
                    <strong><?= $row->datetime ?> - <?= $row->message ?></strong>
                </div>

                <?php if (!empty($row->request) || !empty($row->referer) || !empty($row->ip)) : ?>
                    <div class="margin-10 text-muted">
                        <?php if (!empty($row->request)) : ?>
                            <div class="text-truncated">
                                <?= __d('me_cms', 'Request URL') ?>:
                                <?= $this->Html->link(
                                    $row->request === '/' ? '(Root)' : $row->request,
                                    $row->request,
                                    ['target' => '_blank']
                                ) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($row->referer)) : ?>
                            <div class="text-truncated">
                                <?= __d('me_cms', 'Referer URL') ?>:
                                <?= $this->Html->link($row->referer, $row->referer, ['target' => '_blank']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($row->ip)) : ?>
                            <div>
                                <?= __d('me_cms', 'Client IP') ?>:
                                <?= $row->ip ?>
                                <?= sprintf(
                                    '(%s | %s)',
                                    $this->Html->link(
                                        __d('me_cms', 'Who is'),
                                        str_replace('{IP}', $row->ip, getConfigOrFail('security.ip_whois')),
                                        ['target' => '_blank']
                                    ),
                                    $this->Html->link(
                                        __d('me_cms', 'Map'),
                                        str_replace('{IP}', $row->ip, getConfigOrFail('security.ip_map')),
                                        ['target' => '_blank']
                                    )
                                ) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                $buttons = $collapse = [];

                if (!empty($row->attributes)) {
                    $buttons[] = $this->Html->tag('button', __d('me_cms', 'Exception attributes'), [
                        'class' => 'btn-sm btn-primary',
                        'data-toggle' => 'collapse',
                        'data-target' => "#log-attributes-{$k}",
                    ]);
                    $collapse[] = $this->Html->div(
                        'collapse',
                        $this->Html->pre($row->attributes),
                        ['id' => "log-attributes-{$k}"]
                    );
                }

                if (!empty($row->trace)) {
                    $buttons[] = $this->Html->tag('button', __d('me_cms', 'Trace'), [
                        'class' => 'btn-sm btn-primary',
                        'data-toggle' => 'collapse',
                        'data-target' => "#log-trace-{$k}",
                    ]);
                    $collapse[] = $this->Html->div(
                        'collapse',
                        $this->Html->pre($row->trace),
                        ['id' => "log-trace-{$k}"]
                    );
                }

                $buttons[] = $this->Html->tag('button', __d('me_cms', 'Full log'), [
                    'class' => 'btn-sm btn-primary',
                    'data-toggle' => 'collapse',
                    'data-target' => "#log-full-{$k}",
                ]);
                $collapse[] = $this->Html->div(
                    'collapse',
                    $this->Html->pre($row->full),
                    ['id' => "log-full-{$k}"]
                );

                echo $this->Html->div('btn-group margin-10', implode(PHP_EOL, $buttons), ['role' => 'group']);
                echo implode(PHP_EOL, $collapse);
                ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>