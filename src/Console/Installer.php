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
	 * Creates the `robots.txt` file
     * @param \Composer\IO\IOInterface $io IO interface to write to console
	 */
	public static function createRobots($io) {
		if(file_exists($file = WWW_ROOT.'robots.txt'))
			return;
		
		if((new \Cake\Filesystem\File($file, TRUE))
			->write('User-agent: *
				Disallow: /admin/
				Disallow: /ckeditor/
				Disallow: /css/
				Disallow: /js/
				Disallow: /vendor/'))
			$io->write(sprintf('Created `%s` file', str_replace(ROOT.DS, NULL, $file)));
		else
			$io->write(sprintf('<error>Failed to create `%s` file</error>', str_replace(ROOT.DS, NULL, $file)));
	}
	
	/**
	 * Fixes KCFinder.
	 * Creates the file `vendor/kcfinder/.htaccess`
     * @param \Composer\IO\IOInterface $io IO interface to write to console
	 * @see http://kcfinder.sunhater.com/integrate
	 */
	public static function fixKcfinder($io) {
		//Returns, if the file already exists
		if(file_exists($file = WWW_ROOT.'vendor'.DS.'kcfinder'.DS.'.htaccess'))
			return;
		
		if((new \Cake\Filesystem\File($file, TRUE))
			->write('<IfModule mod_php5.c>
					php_value session.cache_limiter must-revalidate
					php_value session.cookie_httponly On
					php_value session.cookie_lifetime 14400
					php_value session.gc_maxlifetime 14400
					php_value session.name CAKEPHP
				</IfModule>'))
			$io->write(sprintf('Created `%s` file', str_replace(ROOT.DS, NULL, $file)));
		else
			$io->write(sprintf('<error>Failed to create `%s` file</error>', str_replace(ROOT.DS, NULL, $file)));
	}
	
	/**
	 * Occurs after the autoloader has been dumped, either during install/update, or via the dump-autoload command.
     * @param \Composer\Script\Event $event The composer event object
	 * @uses MeTools\Console\Installer::postAutoloadDump()
	 * @uses createRobots()
	 * @uses fixKcfinder()
	 * @uses MeTools\Console\Installer::$links
	 * @uses MeTools\Console\Installer::$paths
	 */
	public static function postAutoloadDump(Event $event) {
		//Merges assets for which create symbolic links
		parent::$links = array_merge(parent::$links, [
			'components/jquery-cookie'	=> 'jquery-cookie',
			'newerton/fancy-box/source'	=> 'fancybox',
			'sunhater/kcfinder'			=> 'kcfinder'
		]);
		
		//Merges paths to be created and made writable
		parent::$paths = array_merge(parent::$paths, [
			WWW_ROOT.'img'.DS.'banners',
			WWW_ROOT.'img'.DS.'photos'
		]);
		
		parent::postAutoloadDump($event);
		
        $io = $event->getIO();
		
		//If the shell is interactive
        if($io->isInteractive()) {
            $validator = function($arg) {
                if(in_array($arg, ['Y', 'y', 'N', 'n']))
                    return $arg;
				
                throw new Exception('This is not a valid answer. Please choose Y or n.');
            };
			
			//Asks if the `robots.txt` file should be created
            $ask = $io->askAndValidate('<info>Create `robots.txt` file? (Default to Y)</info> [<comment>Y, n</comment>]? ', $validator, 10, 'Y');

            if(in_array($ask, ['Y', 'y']))
				self::createRobots($io);
			
			//Asks if KCFinder shuold be fixed
			$ask = $io->askAndValidate('<info>Fix KCFinder? (Default to Y)</info> [<comment>Y, n</comment>]? ', $validator, 10, 'Y');
			
            if(in_array($ask, ['Y', 'y']))
				self::fixKcfinder($io);
        }
		else {
			self::createRobots($io);
			self::fixKcfinder($io);
		}
	}
}