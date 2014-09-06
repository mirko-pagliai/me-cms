<?php

/**
 * Album utility
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
 * An utility to manage photo albums and photos.
 * 
 * You can use this utility by adding in your controller:
 * <code>
 * App::uses('Album', 'MeCms.Utility');
 * </code>
 */
class Album {
	/**
	 * Checks if the directory album is writeable.
	 * 
	 * If an album ID is specified, it checks the directory of that album.
	 * Otherwise, it checks the parent directory.
	 * @param int $albumId Album ID
	 * @return boolean TRUE if is writeable, otherwise FALSE;
	 * @uses getAlbumPath() to get the album path
	 */
	static public function albumIsWriteable($albumId = NULL) {
		//Checks if the album directory exists and is writable
		if(is_writable($path = self::getAlbumPath($albumId)))
			return TRUE;

		if(!empty($albumId)) {
			//Creates the directory and make it writable
			$folder = new Folder();
			return (bool) @$folder->create($path, '0777');
		}
		
		return FALSE;
	}
	
	/**
	 * Creates the album directory
	 * @param int $albumId Album id
	 * @return boolean TRUE if the directory was created, otherwise FALSE
	 * @uses getAlbumPath() to get the album path
	 */
	static public function createAlbum($albumId) {
		//Creates the directory and make it writable
		$folder = new Folder();
		return (bool) @$folder->create(self::getAlbumPath($albumId), 0755);
	}
	
	/**
	 * Deletes and album
	 * @param int $albumId Album ID
	 * @return TRUE if the album has been deleted, otherwise FALSE
	 * @uses getAlbumPath() to get the album path
	 */
	static public function deleteAlbum($albumId) {
		$folder = new Folder(self::getAlbumPath($albumId));
		return $folder->delete();
	}
	
	/**
	 * Deletes a photo
	 * @param string $filename Photo filename
	 * @param int $albumId Album ID
	 * @return TRUE if the photo has been deleted, otherwise FALSE
	 * @uses getAlbumPath() to get the album path
	 */
	static public function deletePhoto($filename, $albumId) {
		$file = new File(self::getAlbumPath($albumId).DS.$filename);
		return $file->delete();
	}
	
	/**
	 * Gets the path of an album.
	 * 
	 * If an album ID is specified, it returns the path of that album.
	 * Otherwise, it returns the path of the parent directory.
	 * @param int $albumId Album ID
	 * @return string Path
	 */
	static public function getAlbumPath($albumId = NULL) {
		return WWW_ROOT.'img'.DS.'photos'.DS.$albumId;
	}
	
	/**
	 * Gets the list of the photos in the temporary directory (`APP/tmp/photos`).
	 * @return array Photos list
	 * @uses getTmpPath() to get the path of the photos temporary directory
	 */
	static public function getTmp() {
		$dir = new Folder(self::getTmpPath());
		return $dir->find('.*\.(gif|jpg|jpeg|png)', TRUE);	
	}
	
	/**
	 * Gets the path of the temporary directory
	 * @return string Path
	 */
	static public function getTmpPath() {
		return TMP.'photos';
	}
	
	/**
	 * Saves a photo from the temporary directory to the album directory.
	 * @param string $filename Photo filename, relative to the temporary directory
	 * @param int $albumId Album ID
	 * @return boolean TRUE if the photo has been saved, otherwise FALSE
	 * @uses getTmpPath() to get the path of the temporary directory
	 * @uses getAlbumPath() to get the album path
	 */
	static public function savePhoto($filename, $albumId) {
		$file = new File(self::getTmpPath().DS.$filename);
		if($success = $file->copy(self::getAlbumPath($albumId).DS.$filename))
			$file->delete();
		
		return $success;
	}
}