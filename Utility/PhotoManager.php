<?php
/**
 * PhotoManager utility
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
 * An utility to manage photos.
 * 
 * You can use this utility by adding in your controller:
 * <code>
 * App::uses('PhotoManager', 'MeCms.Utility');
 * </code>
 */
class PhotoManager {
	/**
	 * Creates the album folder.
	 * @param int $albumId Album ID
	 * @return boolean TRUE if the directory was created, otherwise FALSE
	 * @uses getFolder()
	 */
	static public function createFolder($albumId) {
		//Creates the directory and make it writable
		$folder = new Folder();
		return (bool) $folder->create(self::getFolder($albumId), 0777);
	}
	
	/**
	 * Deletes a file.
	 * @param string $filename File name
	 * @param int $albumId Album ID
	 * @return TRUE if the photo has been deleted, otherwise FALSE
	 * @uses getFolder()
	 */
	static public function delete($filename, $albumId) {
		$file = new File(self::getFolder($albumId).DS.$filename);
		return $file->delete();
	}
	
	/**
	 * Deletes an album folder.
	 * @param int $albumId Album ID
	 * @return TRUE if the album has been deleted, otherwise FALSE
	 * @uses getFolder()
	 */
	static public function deleteFolder($albumId) {
		$folder = new Folder(self::getFolder($albumId));
		return $folder->delete();
	}
	
	/**
	 * Checks if the main folder is writable.
	 * 
	 * If an album ID is specified, it checks if the album folder is writable.
	 * @param int $albumId Album ID
	 * @return boolean TRUE if is writeble, otherwise FALSE
	 * @uses createFolder()
	 * @uses getFolder()
	 */
	static public function folderIsWritable($albumId = NULL) {
		//Checks if the folder is writable
		if(is_writable($path = self::getFolder($albumId)))
			return TRUE;

		//If the album ID is specified, creates the album folder
		if(!empty($albumId))
			return self::createFolder($albumId);
		
		return FALSE;
	}
	
	/**
	 * Gets the main folder path.
	 * 
	 * If an album ID is specified, it returns the album folder path.
	 * @param int $albumId Album ID
	 * @return string Folder path
	 */
	static public function getFolder($albumId = NULL) {
		$path = WWW_ROOT.'img'.DS.'photos';
		
		if(!empty($albumId))
			$path .= DS.$albumId;
		
		return $path;
	}
	
	/**
	 * Gets the full path for a file.
	 * @param string $filename File name
	 * @param int $albumId Album ID
	 * @return string File path
	 * @uses getFolder()
	 */
	static public function getPath($filename, $albumId) {
		return self::getFolder($albumId).DS.$filename;
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
		return TMP.'uploads'.DS.'photos';
	}
	
	/**
	 * Saves a file.
	 * @param string $filename File name
	 * @param int $albumId Album ID
	 * @return boolean TRUE if the file has been saved, otherwise FALSE
	 * @uses getTmpPath()
	 * @uses getFolder()
	 */
	static public function save($filename, $albumId) {
		$file = new File(self::getTmpPath().DS.$filename);
		if($success = $file->copy(self::getFolder($albumId).DS.$filename))
			$file->delete();
		
		return $success;
	}
}