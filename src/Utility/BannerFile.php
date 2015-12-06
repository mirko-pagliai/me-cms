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
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * An utility to manage banners.
 * 
 * You can use this utility by adding:
 * <code>
 * use MeCms\Utility\BannerFile;
 * </code>
 */
class BannerFile {
    /**
     * Alias for `checkFolder()` method
     * @see checkFolder()
     */
    public static function check() {
        return call_user_func_array([get_class(), 'checkFolder'], func_get_args());
    }
	
	/**
	 * Checks if the folder is writable
	 * @return boolean
	 * @uses folder()
	 */
	public static function checkFolder() {
		return folder_is_writable(self::folder());
	}
	
	/**
	 * Deletes a file
	 * @param string $filename Filename
	 * @return bool
	 * @uses path()
	 */
	public static function delete($filename) {
		$file = new File(self::path($filename));
		return $file->delete();
	}
	
    /**
     * Alias for `getFolder()` method
     * @see getFolder()
     */
    public static function folder() {
        return call_user_func_array([get_class(), 'getFolder'], func_get_args());
    }
	
	/**
	 * Gets the main folder path
	 * @return string Folder path
	 */
	public static function getFolder() {
		return WWW_ROOT.'img'.DS.'banners'.DS;
	}
	
	/**
	 * Gets the file full path
	 * @param string $filename File name
	 * @return string Path
	 * @uses folder()
	 */
	public static function getPath($filename) {
		return self::folder().DS.$filename;
	}
	
    /**
     * Alias for `getPath()` method
     * @see getPath()
     */
    public static function path() {
        return call_user_func_array([get_class(), 'getPath'], func_get_args());
    }
}