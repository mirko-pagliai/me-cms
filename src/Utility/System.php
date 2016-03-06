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

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use MeTools\Utility\System as BaseSystem;

/**
 * An utility for checking the status of the system and perform maintenance tasks.
 */
class System extends BaseSystem {
	/**
	 * Clears assets
	 * @return boolean
	 */
	public static function clearAssets() {
		if(!folder_is_writable(ASSETS))
			return FALSE;
		
		$success = TRUE;
		
		//Deletes each file
		foreach((new Folder(ASSETS))->read(FALSE, ['empty'])[1] as $file) {
			if(!(new File(ASSETS.DS.$file))->delete())
				$success = FALSE;
		}
		
		return $success;
	}
	
    /**
     * Clears thumbnails
     * @return boolean
     */
    public static function clearThumbs() {
		if(!folder_is_writable(THUMBS))
			return FALSE;
		
		$success = TRUE;
		
		//Deletes each file
		foreach((new Folder(THUMBS))->read(FALSE, ['empty'])[1] as $file) {
			if(!(new File(THUMBS.DS.$file))->delete())
				$success = FALSE;
		}
		
		return $success;
    }
}