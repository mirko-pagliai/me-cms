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
 * @package		MeCms\View\Posts\rss
 */
?>
	
<?php
	$this->set([
		'documentData'	=> ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'],
		'channelData'	=> [
			'title'			=> __d('me_cms', 'Latest posts'),
			'link'			=> \Cake\Routing\Router::url('/', TRUE),
			'description'	=> __d('me_cms', 'Latest posts'),
			'language'		=> 'en-us'
		]
	]);
	
	foreach($posts as $post) {
		//Sets link
		$link = ['_name' => 'post', $post->slug];
		
		//Executes BBCode on the text
		$post->text = $this->BBCode->parser($post->text);
		
		//Truncates the text if the "<!-- read-more -->" tag is present
		if($strpos = strpos($post->text, '<!-- read-more -->'))
			$post->text = $this->Text->truncate($post->text, $strpos, ['exact' => TRUE, 'html' => FALSE]);
		//Truncates the text if requested by the configuration
		elseif(config('frontend.truncate_to'))
			$post->text = $this->Text->truncate($post->text, config('frontend.truncate_to'), ['exact' => FALSE, 'html' => TRUE]);
			
		//Strips tags
		$post->text = strip_tags($post->text);
		
		//Adds the preview image
		if(!empty($post['Post']['preview']))
			$post->text = sprintf('%s%s', $this->Thumb->img($post['Post']['preview'], ['width' => 200]), $post->text);

		echo $this->Rss->item([], [
			'description'	=> $post->text,
			'guid'			=> ['url' => $link, 'isPermaLink' => 'true'],
			'link'			=> $link,
			'pubDate'		=> $post->created,
			'title'			=> $post->title
		]);
	}
?>