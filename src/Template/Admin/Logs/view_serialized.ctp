<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
    $this->extend('/Admin/Common/view');
    $this->assign('title', __d('me_cms', 'Log {0}', $log->filename));
?>

<?php if(!empty($log->content)): ?>
    <div class="as-table">
        <?php foreach($log->content as $k => $row): ?>
            <div class="padding-10 small">
                <?php
                    if(in_array($row->level, ['error', 'fatal'])) {
                        $class = 'bg-danger text-danger';
                    }
                    elseif(in_array($row->level, ['warning', 'notice'])) {
                        $class = 'bg-warning text-warning';
                    }
                    else {
                        $class = 'bg-info text-info';
                    }
                ?>

                <div class="<?= $class ?> margin-10 padding-10">
                    <strong><?= $row->datetime ?> - <?= $row->message ?></strong>
                </div>

                <?php if(!empty($row->request) || !empty($row->referer) || !empty($row->ip)): ?>
                    <div class="margin-10 text-muted">
                        <?php if(!empty($row->request)): ?>
                            <div class="text-truncated">
                                <?= __d('me_cms', 'Request URL') ?>: 
                                <?= $this->Html->link($row->request === '/' ? '(Root)' : $row->request, $row->request, ['target' => '_blank']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($row->referer)): ?>
                            <div class="text-truncated">
                                <?= __d('me_cms', 'Referer URL') ?>: 
                                <?= $this->Html->link($row->referer, $row->referer, ['target' => '_blank']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($row->ip)): ?>
                            <div>
                                <?= __d('me_cms', 'Client IP') ?>: 
                                <?= $row->ip ?> 
                                (<?= $this->Html->link(__d('me_cms', 'Who is'), str_replace('{IP}', $row->ip, config('security.ip_whois')), ['target' => '_blank']) ?> | 
                                    <?= $this->Html->link(__d('me_cms', 'Map'), str_replace('{IP}', $row->ip, config('security.ip_map')), ['target' => '_blank']) ?>)
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                    $buttons = $collapse = [];

                    if(!empty($row->attributes)) {
                        $buttons[] = $this->Html->tag('button', __d('me_cms', 'Exception attributes'), ['class' => 'btn-sm btn-primary', 'data-toggle' => 'collapse', 'data-target' => "#log-attributes-{$k}"]);
                        $collapse[] = $this->Html->div('collapse', $this->Html->pre($row->attributes), ['id' => "log-attributes-{$k}"]);
                    }

                    if(!empty($row->trace)) {
                        $buttons[] = $this->Html->tag('button', __d('me_cms', 'Trace'), ['class' => 'btn-sm btn-primary', 'data-toggle' => 'collapse', 'data-target' => "#log-trace-{$k}"]);
                        $collapse[] = $this->Html->div('collapse', $this->Html->pre($row->trace), ['id' => "log-trace-{$k}"]);
                    }

                    $buttons[] = $this->Html->tag('button', __d('me_cms', 'Full log'), ['class' => 'btn-sm btn-primary', 'data-toggle' => 'collapse', 'data-target' => "#log-full-{$k}"]);
                    $collapse[] = $this->Html->div('collapse', $this->Html->pre($row->full), ['id' => "log-full-{$k}"]);

                    echo $this->Html->div('btn-group margin-10', implode(PHP_EOL, $buttons), ['role' => 'group']);
                    echo implode(PHP_EOL, $collapse);
                ?>					
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>