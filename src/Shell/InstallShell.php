<?php
/**
 * This file is part of MeTools.
 *
 * MeTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeTools.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Shell;

use MeTools\Core\Plugin;
use MeTools\Shell\InstallShell as BaseShell;

/**
 * Executes some tasks to make the system ready to work
 */
class InstallShell extends BaseShell {
	/**
	 * Construct.
	 * @uses $links
	 * @uses $packages
	 * @uses $paths
	 */
	public function __construct() {
		parent::__construct();
		
		//Merges assets for which create symbolic links
		$this->links = am($this->links, [
			'components/jquery-cookie'	=> 'jquery-cookie',
			'sunhater/kcfinder'			=> 'kcfinder'
		]);
		
		$this->packages = am($this->packages, [
			'sunhater/kcfinder'
		]);
		
		//Merges paths to be created and made writable
		$this->paths = am($this->paths, [
			WWW_ROOT.'img'.DS.'banners',
			WWW_ROOT.'img'.DS.'photos'
		]);
	}
	
	/**
	 * Executes all available tasks
	 * @uses MeTools\Shell\InstallShell::all()
	 * @uses copyConfig()
	 * @uses fixKcfinder()
	 */
	public function all() {
		parent::all();
		
		if($this->param('force')) {
			$this->copyConfig();
			$this->fixKcfinder();
			
			return;
		}
		
		$ask = $this->in(__d('me_cms', 'Copy configuration files?'), ['Y', 'n'], 'Y');
		if(in_array($ask, ['Y', 'y']))
			$this->copyConfig();
		
		$ask = $this->in(__d('me_tools', 'Fix `{0}`?', 'KCFinder'), ['Y', 'n'], 'Y');
		if(in_array($ask, ['Y', 'y']))
			$this->fixKcfinder();
	}
	
	/**
	 * Copies the configuration files
	 */
	public function copyConfig() {
		$files = [
			'me_cms.php',
			'widgets.php'
		];
		
		foreach($files as $file) {
			if(file_exists($target = ROOT.DS.'config'.DS.$file)) {
				$this->verbose(__d('me_tools', 'The file `{0}` already exists', rtr($file)));
				continue;
			}
						
			if(@copy(Plugin::path('MeCms', 'config'.DS.$file), $target))
				$this->verbose(__d('me_tools', 'The file `{0}` has been copied', rtr($target)));
			else
				$this->err(__d('me_tools', 'The file `{0}` has not been copied', rtr($target)));
		}
	}
	
	/**
	 * Fixes KCFinder.
	 * Creates the file `vendor/kcfinder/.htaccess`
	 * @see http://kcfinder.sunhater.com/integrate
	 */
	public function fixKcfinder() {
		//Checks for KCFinder
		if(!is_readable($file = WWW_ROOT.'vendor'.DS.'kcfinder'))
			return $this->err(__d('me_tools', 'I can\'t find `{0}`', 'KCFinder'));
		
		if(is_readable($file = WWW_ROOT.'vendor'.DS.'kcfinder'.DS.'.htaccess'))
			return $this->verbose(__d('me_tools', 'The file `{0}` already exists', rtr($file)));
		
		if($this->createFile($file, '<IfModule mod_php5.c>
			php_value session.cache_limiter must-revalidate
			php_value session.cookie_httponly On
			php_value session.cookie_lifetime 14400
			php_value session.gc_maxlifetime 14400
			php_value session.name CAKEPHP
		</IfModule>'))
			$this->verbose(__d('me_tools', 'The file `{0}` has been created', rtr($file)));
		else
			$this->err(__d('me_tools', 'The file `{0}` has not been created', rtr($file)));
	}
	
	/**
	 * Gets the option parser instance and configures it.
	 * @return ConsoleOptionParser
	 * @uses MeTools\Shell\InstallShell::getOptionParser()
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		
		return $parser->addSubcommands([
			'copyConfig'	=> ['help' => __d('me_cms', 'it copies the configuration file')],
			'fixKcfinder'	=> ['help' => __d('me_cms', 'it fixes {0}', 'KCFinder')]
		]);
	}
}