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
		
		//Sets text
		$text = $this->Text->truncate(
			strip_tags($post->text),
			config('frontend.truncate_to'),
			['ending' => '...', 'exact' => FALSE, 'html' => TRUE]
		);
		
		//Adds the preview image
		if(!empty($post['Post']['preview']))
			$text = sprintf('%s%s', $this->Thumb->img($post['Post']['preview'], ['width' => 200]), $text);

		echo $this->Rss->item([], [
			'description'	=> $text,
			'guid'			=> ['url' => $link, 'isPermaLink' => 'true'],
			'link'			=> $link,
			'pubDate'		=> $post->created,
			'title'			=> $post->title
		]);
	}
?>