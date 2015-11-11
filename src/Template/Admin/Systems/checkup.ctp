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
		
		//MeCms version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('MeCMS'), $plugins['mecms_version']));
		
		//CakePHP version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('CakePHP'), $plugins['cakephp_version']));
		
		echo $this->Html->h4(__d('me_cms', 'Plugins'));
		//Plugins version
		foreach($plugins['plugins_version'] as $plugin)
			echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} plugin version: {1}', $this->Html->strong($plugin['name']), $plugin['version']));
	
		echo $this->Html->h4('Apache');
		//Current version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('Apache'), $apache['current_version']));
		
		//Checks for Apache's modules
		foreach(['rewrite', 'expires'] as $mod) {
			if(is_bool($apache[$mod]) && $apache[$mod])
				echo $this->Html->para($successClasses, __d('me_cms', 'The {0} module is enabled', $this->Html->strong($mod)), $successOptions);
			elseif(is_bool($apache[$mod]) && !$apache[$mod])
				echo $this->Html->para($errorClasses, __d('me_cms', 'The {0} module is not enabled', $this->Html->strong($mod)), $errorOptions);
			else
				echo $this->Html->para($warningClasses, __d('me_cms', 'The {0} module cannot be checked', $this->Html->strong($mod)), $warningOptions);
		}
		
		echo $this->Html->h4('PHP');
		//Current version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '{0} version: {1}', $this->Html->strong('PHP'), $php['current_version']));
		//Check version
		if($php['check_version'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The {0} version is at least {1}', $this->Html->strong('PHP'), $this->Html->strong($php['required_version'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The {0} version is less than {1}', $this->Html->strong('PHP'), $this->Html->strong($php['required_version'])), $errorOptions);
		
		//Checks for PHP's extensions
		foreach(['exif', 'imagick', 'mbstring', 'mcrypt', 'zip'] as $ext) {
			if($php[$ext])
				echo $this->Html->para($successClasses, __d('me_cms', 'The {0} extension is enabled', $this->Html->strong($ext)), $successOptions);
			else
				echo $this->Html->para($errorClasses, __d('me_cms', 'The {0} extension is not enabled', $this->Html->strong($ext)), $errorOptions);
		}
		
		echo $this->Html->h4('ffmpegthumbnailer');
		if($ffmpegthumbnailer['check'])
			echo $this->Html->para($successClasses, __d('me_cms', '{0} is available', $this->Html->strong('ffmpegthumbnailer')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', '{0} is not available', $this->Html->strong('ffmpegthumbnailer')), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Webroot'));
		//Banners directory is writable
		if($banners['check'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($banners['path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($banners['path'])), $errorOptions);
		//Files directory is writable
		if($files['check'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($files['path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($files['path'])), $errorOptions);
			
		//Photos directory is writable
		if($photos['check'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($photos['path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($photos['path'])), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Thumbs'));
		//Photos thumbs are writable
		if($thumbs['photos_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($thumbs['photos_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($thumbs['photos_path'])), $errorOptions);
		//Remotes thumbs are writable
		if($thumbs['remotes_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($thumbs['remotes_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($thumbs['remotes_path'])), $errorOptions);
		//Videos thumbs are writable
		if($thumbs['videos_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($thumbs['videos_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($thumbs['videos_path'])), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Temporary directories'));
		//Tmp is writable
		if($tmp['tmp_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The temporary directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The temporary directory is not readable or writable'), $errorOptions);
		//Cache status
		if($tmp['cache_status'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The cache is enabled'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The cache is disabled or debugging is active'), $errorOptions);
		//Cache is writable
		if($tmp['cache_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The cache is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The cache is not readable or writable'), $errorOptions);
		//Logs are writable
		if($tmp['logs_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($tmp['logs_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory {0} is not readable or writable', $this->Html->code($tmp['logs_path'])), $errorOptions);
	?>
</div>