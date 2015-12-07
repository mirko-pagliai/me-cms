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
?>
	
<?php
	$this->assign('title', __d('me_cms', 'System checkup'));
	
	//Sets some classes and options
	$successClasses = 'bg-success text-success padding10';
	$errorClasses = 'bg-danger text-danger padding10';
	$warningClasses = 'bg-warning text-warning padding10';
	$successOptions = ['icon' => 'check'];
	$errorOptions = ['icon' => 'times'];
	$warningOptions = ['icon' => 'check'];
?>

<div class="systems index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'System checkup'));
		
		/* -------------------------------- */
		/*			MeCms version			*/
		/* -------------------------------- */
		$text = $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('MeCMS'), $plugins['mecms_version']));
		echo $this->Html->div('col-sm-12', $text);
		
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*			CakePHP version			*/
		/* -------------------------------- */
		$text = $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('CakePHP'), $plugins['cakephp_version']));
		echo $this->Html->div('col-sm-12', $text);
				
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*			Cache status			*/
		/* -------------------------------- */
		if($cache['status'])
			$text = $this->Html->para($successClasses, __d('me_cms', 'The cache is enabled'), $successOptions);
		else
			$text = $this->Html->para($errorClasses, __d('me_cms', 'The cache is disabled or debugging is active'), $errorOptions);
		echo $this->Html->div('col-sm-12', $text);
				
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*				Plugins				*/
		/* -------------------------------- */
		echo $this->Html->h4(__d('me_cms', 'Plugins'));
		
		//Plugins version
		foreach($plugins['plugins_version'] as $plugin) {
			$text = $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} plugin version: {1}', $this->Html->strong($plugin['name']), $plugin['version']));
			echo $this->Html->div('col-sm-6', $text);
		}
		
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*				Apache				*/
		/* -------------------------------- */
		echo $this->Html->h4('Apache');
		//Current version
		$text = $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('Apache'), $apache['current_version']));
		echo $this->Html->div('col-sm-12', $text);
		
		//Apache's modules
		foreach(['rewrite', 'expires'] as $mod) {
			if(is_bool($apache[$mod]) && $apache[$mod])
				$text = $this->Html->para($successClasses, __d('me_cms', 'The {0} module is enabled', $this->Html->strong($mod)), $successOptions);
			elseif(is_bool($apache[$mod]) && !$apache[$mod])
				$text = $this->Html->para($errorClasses, __d('me_cms', 'The {0} module is not enabled', $this->Html->strong($mod)), $errorOptions);
			else
				$text = $this->Html->para($warningClasses, __d('me_cms', 'The {0} module cannot be checked', $this->Html->strong($mod)), $warningOptions);
			
			echo $this->Html->div('col-sm-6', $text);
		}
		
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*				PHP					*/
		/* -------------------------------- */
		echo $this->Html->h4('PHP');
		//Current version
		$text = $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('PHP'), $php['current_version']));
		echo $this->Html->div('col-sm-12', $text);
		
		//Check version
		if($php['check_version'])
			$text = $this->Html->para($successClasses, __d('me_cms', 'The {0} version is at least {1}', $this->Html->strong('PHP'), $this->Html->strong($php['required_version'])), $successOptions);
		else
			$text = $this->Html->para($errorClasses, __d('me_cms', 'The {0} version is less than {1}', $this->Html->strong('PHP'), $this->Html->strong($php['required_version'])), $errorOptions);
		echo $this->Html->div('col-sm-12', $text);
		
		//PHP's extensions
		foreach(['exif', 'imagick', 'mbstring', 'mcrypt', 'zip'] as $ext) {
			if($php[$ext])
				$text = $this->Html->para($successClasses, __d('me_cms', 'The {0} extension is enabled', $this->Html->strong($ext)), $successOptions);
			else
				$text = $this->Html->para($errorClasses, __d('me_cms', 'The {0} extension is not enabled', $this->Html->strong($ext)), $errorOptions);
		
			echo $this->Html->div('col-sm-6', $text);
		}
			
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*		ffmpegthumbnailer			*/
		/* -------------------------------- */
		echo $this->Html->h4('ffmpegthumbnailer');
		if($ffmpegthumbnailer['check'])
			$text = $this->Html->para($successClasses, __d('me_cms', '{0} is available', $this->Html->strong('ffmpegthumbnailer')), $successOptions);
		else
			$text = $this->Html->para($errorClasses, __d('me_cms', '{0} is not available', $this->Html->strong('ffmpegthumbnailer')), $errorOptions);
		echo $this->Html->div('col-sm-12', $text);
			
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*				Webroot				*/
		/* -------------------------------- */
		echo $this->Html->h4(__d('me_cms', 'Webroot'));
		
		//Webroot directories
		foreach($webroot as $dir) {
			if($dir['writeable'])
				$text = $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($dir['path'])), $successOptions);
			else
				$text =  $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($dir['path'])), $errorOptions);
			echo $this->Html->div('col-sm-6', $text);
		}
			
		echo $this->Html->div('clearfix');
		
		/* -------------------------------- */
		/*			Temporary				*/
		/* -------------------------------- */
		echo $this->Html->h4(__d('me_cms', 'Temporary directories'));
		
		//Temporary directories
		foreach($temporary as $dir) {
			if($dir['writeable'])
				$text = $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($dir['path'])), $successOptions);
			else
				$text =  $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($dir['path'])), $errorOptions);
			echo $this->Html->div('col-sm-6', $text);
		}
	?>
</div>