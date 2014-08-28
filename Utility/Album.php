<?php

/**
 * Album utility
 *
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Utility
 */

App::uses('Folder', 'Utility');

/**
 * An utility to manage photo albums and photos
 * 
 * You can use this utility by adding in your controller:
 * <code>
 * App::uses('Album', 'MeCmsBackend.Utility');
 * </code>
 */
class Album {
	/**
	 * Checks if an album directory is writeable.
	 * 
	 * If an album ID is specified, it checks the directory of that album.
	 * Otherwise, it checks the album parent directory.
	 * @param string $albumId Album ID
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
		return (bool) @$folder->create(self::getAlbumPath($albumId), '0777');
	}
	
	/**
	 * Gets the path of an album.
	 * 
	 * If an album ID is specified, it returns the path of that album.
	 * Otherwise, it returns the path of the album parent directory.
	 * @param string $albumId Album ID
	 * @return string Path
	 */
	static public function getAlbumPath($albumId = NULL) {
		return Configure::read('MeCmsBackend.photos.path').DS.$albumId;
	}
	
	/**
	 * Gets the list of the photos in the temporary directory (`APP/tmp/photos`)
	 * @return array Photos list
	 * @uses getTmpPath() to get the path of the photos temporary directory
	 */
	static public function getTmp() {
		$dir = new Folder(self::getTmpPath());
		return $dir->find('.*\.(gif|jpg|jpeg|png)', TRUE);	
	}
	
	/**
	 * Gets the path of the photos temporary directory
	 * @return string Path
	 */
	static public function getTmpPath() {
		return TMP.'photos';
	}
}