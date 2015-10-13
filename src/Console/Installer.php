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
namespace MeCms\Console;

use MeTools\Console\Installer as BaseInstaller;
use Composer\Script\Event;

/**
 * Provides installation hooks for when this application is installed via
 * composer. Customize this class to suit your needs.
 */
class Installer extends BaseInstaller {
	/**
	 * Assets for which create symbolic links.
	 * The key must be relative to `vendor/`, the value must be relative to `webroot/vendor/`
	 * @see MeTools\Console\Installer::createSymbolicLinkToVendor()
	 * @var array
	 */
	protected static $linksToAssets = [
		'components/jquery-cookie'	=> 'jquery-cookie',
		'newerton/fancy-box/source'	=> 'fancybox'
	];
	
	/**
	 * Occurs after the autoloader has been dumped, either during install/update, or via the dump-autoload command.
     * @param \Composer\Script\Event $event The composer event object
	 * @uses linksToAssets
	 * @uses MeTools\Console\Installer::linksToAssets
	 * @uses MeTools\Console\Installer::postAutoloadDump()
	 */
	public static function postAutoloadDump(Event $event) {
		//Merges
		parent::$linksToAssets = array_merge(parent::$linksToAssets, self::$linksToAssets);
		
		parent::postAutoloadDump($event);
	}
}