<?php
/**
 * BannerManager utility
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Utility
 */

App::uses('Folder', 'Utility');

/**
 * An utility to manage banners.
 * 
 * You can use this utility by adding in your controller:
 * <code>
 * App::uses('BannerManager', 'MeCms.Utility');
 * </code>
 */
class BannerManager {
	/**
	 * Deletes a file.
	 * @param string $filename File name
	 * @return TRUE if the file has been deleted, otherwise FALSE
	 * @uses getPath()
	 */
	static public function delete($filename) {
		$file = new File(self::getPath($filename));
		return $file->delete();
	}
	
	/**
	 * Checks if the main folder is writable.
	 * @return boolean TRUE if is writable, otherwise FALSE
	 * @uses getFolder()
	 */
	static public function folderIsWritable() {
		return is_writable(self::getFolder());
	}
	
	/**
	 * Gets the main folder path.
	 * @return string Folder path
	 */
	static public function getFolder() {
		return WWW_ROOT.'img'.DS.'banners';
	}
	
	/**
	 * Gets the main folder url.
	 * @return string Folder url
	 */
	static public function getFolderUrl() {
		return 'banners';
	}
	
	/**
	 * Gets the full path for a file.
	 * @param string $filename File name
	 * @return string File path
	 * @uses getFolder()
	 */
	static public function getPath($filename) {
		return self::getFolder().DS.$filename;
	}
	
	/**
	 * Gets the file list of the temporary directory.
	 * @return array File list
	 * @uses getTmpPath()
	 */
	static public function getTmp() {
		$dir = new Folder(self::getTmpPath());
		return $dir->find('.*\.(gif|jpg|jpeg|png)', TRUE);	
	}
	
	/**
	 * Gets the temporary directory path.
	 * @return string Path
	 */
	static public function getTmpPath() {
		return TMP.'uploads'.DS.'banners';
	}
	
	/**
	 * Gets the url for a file.
	 * @param string $filename File name
	 * @return string File url
	 * @uses getFolderUrl()
	 */
	static public function getUrl($filename) {
		return self::getFolderUrl().'/'.$filename;
	}

	/**
	 * Saves a file.
	 * @param string $filename File name
	 * @return boolean TRUE if the file has been saved, otherwise FALSE
	 * @uses getFolder()
	 * @uses getTmpPath()
	 */
	static public function save($filename) {
		$file = new File(self::getTmpPath().DS.$filename);
		if($success = $file->copy(self::getFolder().DS.$filename))
			$file->delete();
		
		return $success;
	}
}