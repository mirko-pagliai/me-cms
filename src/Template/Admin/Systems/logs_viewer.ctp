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
	
<?php $this->assign('title', __d('me_cms', 'Log viewer')); ?>

<div class="systems index">
	<?= $this->Html->h2(__d('me_cms', 'Log viewer')) ?>
	
	<?php if(!empty($files)): ?>
		<div class="well">
			<?=	$this->Form->createInline(FALSE, ['type' => 'get']) ?>
			<fieldset>
				<?php 
					echo $this->Form->label('file', __d('me_cms', 'Log file'));
					echo $this->Form->input('file', [
						'default'	=> $this->request->query('file'),
						'label'		=> __d('me_cms', 'Log file'),
						'name'		=> 'file',
						'onchange'	=> 'send_form(this)'
					]);
					echo $this->Form->submit(__d('me_cms', 'Select'));
					echo $this->Form->end();
				?>
			</fieldset>
		</div>
	<?php endif; ?>
	
	<?php if(!empty($unserialized_logs)): ?>
		<div class="as-table">
			<?php foreach($unserialized_logs as $log): ?>
				<div class="padding-10 small">
					<?php
						if(in_array($log->level, ['error', 'fatal']))
							$class = 'bg-danger text-danger';
						elseif(in_array($log->level, ['warning', 'notice']))
							$class = 'bg-warning text-warning';
						else
							$class = 'bg-info text-info';
					?>
					
					<div class="<?= $class ?> margin-10 padding-10">
						<strong><?= $log->datetime ?> - <?= $log->message ?></strong>
					</div>
					
					<?php if(!empty($log->request) || !empty($log->referer) || !empty($log->ip)): ?>
						<div class="margin-10 text-muted">
							<?php if(!empty($log->request)): ?>
								<div>
									<?= __d('me_cms', 'Request URL') ?>: 
									<?= $this->Html->link($log->request === '/' ? '(Root)' : $log->request, $log->request, ['target' => '_blank']) ?>
								</div>
							<?php endif; ?>

							<?php if(!empty($log->referer)): ?>
								<div>
									<?= __d('me_cms', 'Referer URL') ?>: 
									<?= $this->Html->link($log->referer, $log->referer, ['target' => '_blank']) ?>
								</div>
							<?php endif; ?>
								
							<?php if(!empty($log->ip)): ?>
								<div>
									<?= __d('me_cms', 'Client IP') ?>: 
									<?= $log->ip ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
					<?php
						$buttons = $collapse = [];
					
						if(!empty($log->attributes)) {
							$buttons[] = $this->Html->button(__d('me_cms', 'Exception attributes'), '#', ['class' => 'btn-sm btn-primary', 'data-toggle' => 'collapse', 'data-target' => '#log-attributes']);
							$collapse[] = $this->Html->div('collapse', $this->Html->pre($log->attributes), ['id' => 'log-attributes']);
						}
						
						if(!empty($log->trace)) {
							$buttons[] = $this->Html->button(__d('me_cms', 'Trace'), '#', ['class' => 'btn-sm btn-primary', 'data-toggle' => 'collapse', 'data-target' => '#log-trace']);
							$collapse[] = $this->Html->div('collapse', $this->Html->pre($log->trace), ['id' => 'log-trace']);
						}
						
						$buttons[] = $this->Html->button(__d('me_cms', 'Full log'), '#', ['class' => 'btn-sm btn-primary', 'data-toggle' => 'collapse', 'data-target' => '#log-full']);
						$collapse[] = $this->Html->div('collapse', $this->Html->pre($log->full), ['id' => 'log-full']);
										
						echo $this->Html->div('btn-group margin-10', implode(PHP_EOL, $buttons), ['role' => 'group']);
						echo implode(PHP_EOL, $collapse);
					?>					
				</div>
			<?php endforeach; ?>
		</div>
	<?php elseif(!empty($plain_logs)): ?>
		<?= $this->Html->pre($plain_logs) ?>
	<?php endif; ?>
</div>