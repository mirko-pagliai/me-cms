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
namespace MeCms\Utility;

/**
 * An utility to get information about YouTube videos.
 * 
 * You can use this utility by adding:
 * <code>
 * use MeYoutube\Utility\Youtube;
 * </code>
 */
class Youtube {
	/**
	 * Parses a video url and returns the video ID
	 * @param string $url Video url
	 * @return mixed Video ID or FALSE
	 */
	public static function getId($url) {
		if(preg_match('/youtube\.com/', $url)) {
			$url = parse_url($url);
			
			if(empty($url['query']))
				return FALSE;
			
			parse_str($url['query'], $url);
				
			return empty($url['v']) ? FALSE : $url['v'];
		}
		elseif(preg_match('/youtu.be\/(.+)$/', $url, $matches))
			return empty($matches[1]) ? FALSE : $matches[1];
		else
			return FALSE;
	}
}