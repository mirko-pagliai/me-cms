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
	$this->assign('title', __d('me_cms', 'Log viewer'));
	$this->Asset->js('MeCms.backend/logs_viewer', ['block' => 'script_bottom']);
?>

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
	
	<?php if(!empty($logs)): ?>
		<div class="as-table">
			<?php foreach($logs as $log): ?>
				<div class="padding-10 small">
					<?php
						if($log->type === 'error' || $log->type === 'fatal')
							$class = 'bg-danger text-danger';
						elseif($log->type === 'warning' || $log->type === 'notice')
							$class = 'bg-warning text-warning';
						else
							$class = 'bg-info text-info';
					?>

					<div class="<?= $class ?> margin-10 padding-10">
						<strong><?= $log->datetime ?> - <?= $log->error ?></strong>
					</div>

					<?php if(!empty($log->url) || !empty($log->referer)): ?>
						<div class="margin-10 text-muted">
							<?php if(!empty($log->url)): ?>
								<div>
									<?= __d('me_cms', 'Request URL') ?>: 
									<?= $this->Html->link($log->url === '/' ? '(Root)' : $log->url, $log->url, ['target' => '_blank']) ?>
								</div>
							<?php endif; ?>

							<?php if(!empty($log->referer)): ?>
								<div>
									<?= __d('me_cms', 'Referer URL') ?>: 
									<?= $this->Html->link($log->referer, $log->referer, ['target' => '_blank']) ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php
						if(!empty($log->attributes) || !empty($log->trace) || !empty($log->stack_trace)) {
							$buttons = $codes = [];

							if(!empty($log->attributes)) {
								$buttons[] = $this->Html->button(__d('me_cms', 'Exception attributes'), '#', ['class' => 'toggle-log-attributes btn-sm btn-primary']);
								$codes[] = $this->Html->pre($log->attributes, ['class' => 'log-attributes']);
							}

							if(!empty($log->trace)) {
								$buttons[] = $this->Html->button(__d('me_cms', 'Trace'), '#', ['class' => 'toggle-log-trace btn-sm btn-primary']);
								$codes[] = $this->Html->pre($log->trace, ['class' => 'log-trace']);
							}

							if(!empty($log->stack_trace)) {
								$buttons[] = $this->Html->button(__d('me_cms', 'Stack trace'), '#', ['class' => 'toggle-log-stack-trace btn-sm btn-primary']);
								$codes[] = $this->Html->pre($log->stack_trace, ['class' => 'log-stack-trace']);
							}

							echo $this->Html->div('btn-group margin-10', implode(PHP_EOL, $buttons), ['role' => 'group']);
							echo implode(PHP_EOL, $codes);
						}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>