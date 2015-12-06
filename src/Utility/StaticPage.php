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
namespace MeCms\Utility;

use Cake\Core\App;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;

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
     * Alias for `getAll()` method
     * @see getAll()
     */
    public static function all() {
        return call_user_func_array([get_class(), 'getAll'], func_get_args());
    }
	
	/**
	 * Checks if a static page exists, using all the passed arguments
	 * @return bool TRUE if exists and is readable, otherwise FALSE
	 * @uses getPath()
	 */
	public static function exists() {
		$args = array_values(func_get_args())[0];
		
		return empty($args) ? FALSE : is_readable(self::path().implode(DS, $args).'.ctp');
	}
	
	/**
	 * Gets all static pages
	 * @return array List of static pages
	 * @uses Cake\Utility\Inflector::humanize()
	 * @uses path()
	 */
	public static function getAll() {
		//Gets static pages
		$dir = new Folder(self::path());
		$files = $dir->findRecursive('^.+\.ctp$', TRUE);
		
		//Sets the path for use with regex
		$pathForRegex = sprintf('/^%s/', preg_quote(self::path(), '/'));
		
		if(!empty($files))
			array_walk($files, function(&$v, $k, $path) {
				//Turns the path into a relative path
				$path = preg_replace($path, NULL, $v);
				//Sets the filename (name without extension)
				$filename = pathinfo($path, PATHINFO_FILENAME);
				//Sets the file title
				$title = Inflector::humanize($filename);
				//Sets the file arguments
				$args = explode(DS, preg_replace('/\.ctp$/', NULL, $path));
								
				$v = ['StaticPage' => compact('args', 'filename', 'path', 'title')];
			}, $pathForRegex);
			
		return $files;
	}
	
	/**
	 * Gets the path for static pages
	 * @return string Path
	 * @uses Cake\Core\App::path()
	 */
	public static function getPath() {
		return array_values(App::path('Template'))[0].'StaticPages'.DS;
	}
	
	/**
	 * Gets the title for a static page, using all the passed arguments.
	 * @return string Page title
	 * @uses Cake\Utility\Inflector::humanize()
	 */
	public static function getTitle() {
		$args = array_values(func_get_args())[0];
		
		return Inflector::humanize(str_replace('-', '_', $args[count($args)-1]));
	}
	
    /**
     * Alias for `getPath()` method
     * @see getPath()
     */
    public static function path() {
        return call_user_func_array([get_class(), 'getPath'], func_get_args());
    }
	
    /**
     * Alias for `getTitle()` method
     * @see getTitle()
     */
    public static function title() {
        return call_user_func_array([get_class(), 'getTitle'], func_get_args());
    }
}