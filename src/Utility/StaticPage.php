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

use Cake\Core\App;
use Cake\Filesystem\Folder;

/**
 * An utility to manage static pages.
 * 
 * Static pages must be located in `APP/View/StaticPages/`.
 * 
 * You can use this utility by adding:
 * <code>
 * use MeCms\Utility\StaticPage;
 * </code>
 */
class StaticPage {
	/**
	 * Gets all static pages
	 * @return array Static pages
	 * @uses MeTools\Core\Plugin::all()
	 * @ses title()
	 */
	public static function all() {
		//Adds APP to paths
		$paths = [array_values(App::path('Template'))[0].'StaticPages'];
				
		//Adds all plugins to paths, adding first MeCms
		foreach(am(['MeCms'], \MeTools\Core\Plugin::all('MeCms')) as $plugin)
			$paths[] = array_values(App::path('Template', $plugin))[0].'StaticPages';
				
		//Gets all static pages
		foreach($paths as $path)
			foreach((new Folder($path))->findRecursive('^.+\.ctp$', TRUE) as $file) {
				$page = new \stdClass();
				$page->filename = pathinfo($file, PATHINFO_FILENAME);
				$page->path = rtr($file);
				$page->slug = preg_replace('/\.ctp$/', '', preg_replace(sprintf('/^%s/', preg_quote($path.DS, DS)), NULL, $file));
				$page->title = self::title(pathinfo($file, PATHINFO_FILENAME));
				
				$files[] = $page;
			}
		
		return $files;
	}
	
	/**
	 * Gets a static page
	 * @param string $slug Slug
	 * @return mixed Static page or FALSE
	 */
	public static function get($slug) {
		//Sets the file (partial) name
		$file = implode(DS, explode('/', $slug));
				
		//Sets the file patterns
		$patterns = [sprintf('%s_%s', $file, \Cake\I18n\I18n::locale()), sprintf('%s', $file)];
		
		//Checks if the page exists in APP
		foreach($patterns as $pattern)
			if(is_readable(array_values(App::path('Template'))[0].'StaticPages'.DS.$pattern.'.ctp'))
				return 'StaticPages'.DS.$pattern;
		
		//Checks if the page exists in all plugins, beginning with MeCms
		foreach(am(['MeCms'], \MeTools\Core\Plugin::all('MeCms')) as $plugin)
			foreach($patterns as $pattern)
				if(is_readable(array_values(App::path('Template', $plugin))[0].'StaticPages'.DS.$pattern.'.ctp'))
					return sprintf('%s.%s', $plugin, 'StaticPages'.DS.$pattern);
				
		return FALSE;		
	}
	
	/**
	 * Gets the title for a static page
	 * @param string $slug Slug
	 * @return string
	 */
	public static function title($slug) {
		$slug = explode('/', $slug);
		
		return \Cake\Utility\Inflector::humanize(str_replace('-', '_', $slug[count($slug)-1]));
	}
}