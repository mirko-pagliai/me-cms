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

<div class="page-container content-container">
	<div class="content-header">
		<?php
			echo $this->Html->h3($this->Html->link($page->title, ['_name' => 'page', $page->slug]), ['class' => 'content-title']);

			if(!empty($page->subtitle))
				echo $this->Html->h4($this->Html->link($page->subtitle, ['_name' => 'page', $page->slug]), ['class' => 'content-subtitle']);
		?>
		<div class="content-info">
			<?php				
				if(config('page.created') && !empty($page->created))
					echo $this->Html->div('content-date',
						__d('me_cms', 'Posted on {0}', $page->created->i18nFormat(config('main.datetime.long'))),
						['icon' => 'clock-o']
					);
			?>
		</div>
	</div>
	<div class="content-text">
		<?php
			//Executes BBCode on the text
			$page->text = $this->BBCode->parser($page->text);
			
			//Truncates the text if the "<!-- read-more -->" tag is present
			if(!$this->request->isAction('view', 'Pages') && $strpos = strpos($page->text, '<!-- read-more -->'))
				echo $truncated_text = $this->Text->truncate($page->text, $strpos, ['ellipsis' => FALSE, 'exact' => TRUE, 'html' => FALSE]);
			//Truncates the text if requested by the configuration
			elseif(!$this->request->isAction('view', 'Pages') && config('frontend.truncate_to'))
				echo $truncated_text = $this->Text->truncate($page->text, config('frontend.truncate_to'), ['exact' => FALSE, 'html' => TRUE]);
			else
				echo $page->text;
		?>
	</div>
	<div class="content-buttons">
		<?php
			//If it was requested to truncate the text and that has been truncated, it shows the "Read more" link
			if(!empty($truncated_text) && $truncated_text !== $page->text)
				echo $this->Html->button(__d('me_cms', 'Read more'), ['_name' => 'post', $page->slug], ['class' => ' readmore']);
		?>
	</div>
	<?php
		if(config('page.shareaholic') && config('shareaholic.app_id'))
			if($this->request->isAction('view', 'Pges') && !$this->request->isAjax())
				echo $this->Html->shareaholic(config('shareaholic.app_id'));
	?>
</div>