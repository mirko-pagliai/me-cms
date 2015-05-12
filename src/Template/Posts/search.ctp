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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>
	
<?php $this->assign('title', __d('me_cms', 'Search posts')); ?>

<div class="posts index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'Search posts'));
		
		echo $this->Form->create(NULL, ['type' => 'get', 'url' => ['_name' => 'search_posts']]);
		echo $this->Form->input('p', [
			'default'		=> $this->request->query('p'),
			'label'			=> FALSE,
			'placeholder'	=> sprintf('%s...', __d('me_cms', 'Search'))
		]);
		echo $this->Form->submit(__d('me_cms', 'Search'), [
			'class' => 'btn-primary visible-lg-inline',
			'icon'	=> 'search'
		]);
		echo $this->Form->end();
		
		if($this->request->query('p'))
			echo $this->Html->div('bg-info margin-20 padding-10', __d('me_cms', 'You have searched for: {0}', $this->Html->em($this->request->query('p'))));
	
		if(!empty($posts)) {			
			foreach($posts as $post) 
				$list[] = $this->Html->div(NULL, implode(PHP_EOL, [
					sprintf('%s - %s', $this->Html->link($post->title, ['_name' => 'post', $post->slug]), $post->created->i18nFormat(config('main.datetime.short'))),
					$this->Html->para('text-justify', $this->Text->truncate(strip_tags($post->text), 350, ['exact' => FALSE, 'html' => TRUE]))
				]));

			echo $this->Html->ul($list, ['icon' => 'caret-right']);
			
			echo $this->element('MeTools.paginator');
		}
	?>
</div>