<div class="posts view">
	<div class="post-container clearfix">
		<div class="post-header">
			<?php
				$urlCategory = Router::url(array($post['Category']['slug']), TRUE);
				echo $this->Html->h4($this->Html->link($post['Category']['title'], $urlCategory), array('class' => 'post-category'));

				$urlPost = Router::url(array('controller' => 'posts', 'action' => 'view', $post['Post']['slug']), TRUE);
				echo $this->Html->h3($this->Html->link($post['Post']['title'], $urlPost), array('class' => 'post-title'));
			?>
			<div class="post-info">
				<?php
					if(!empty($post['User']['first_name']) && !empty($post['User']['last_name'])) {
						$fullName = sprintf('%s %s', $post['User']['first_name'], $post['User']['last_name']);
						echo $this->Html->div('post-author', __d('me_cms', 'Posted by %s', $fullName), array('icon' => 'user'));
					}

					if(!empty($post['Post']['created'])) {
						$created = $this->Time->format($post['Post']['created'], $config['datetime']['long']);
						echo $this->Html->div('post-created', __d('me_cms', 'Posted on %s', $created), array('icon' => 'clock-o'));
					}
				?>
			</div>
		</div>
		<?php echo $this->Html->div('post-content', $post['Post']['text']);	?>
	</div>
</div>