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
 * @package		MeCms\View\Systems
 */
?>

<div class="systems index">
	<?php 
		echo $this->Html->h2(__d('me_cms', 'System checkup'));
	
		//MeCms version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s version: %s', $this->Html->strong('MeCMS'), $plugins['mecms_version']));
		//CakePHP version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s version: %s', $this->Html->strong('CakePHP'), $plugins['cakephp_version']));
		
		echo $this->Html->h4(__d('me_cms', 'Plugins'));
		//Plugins version
		foreach($plugins['plugins_version'] as $plugin)
			echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s plugin version: %s', $this->Html->strong($plugin['name']), $plugin['version']));
		
		$successClasses = 'bg-success text-success padding10';
		$errorClasses = 'bg-danger text-danger padding10';
		$warningClasses = 'bg-warning text-warning padding10';
		$successOptions = array('icon' => 'check');
		$errorOptions = array('icon' => 'times');
		$warningOptions = array('icon' => 'check');

		echo $this->Html->h4('Apache');
		//Current version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s version: %s', $this->Html->strong('Apache'), $apache['current_version']));
		//Rewrite
		if(is_bool($apache['rewrite']) && $apache['rewrite'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s module is enabled', $this->Html->strong('Rewrite')), $successOptions);
		elseif(is_bool($apache['rewrite']) && !$apache['rewrite'])
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s module is not enabled', $this->Html->strong('Rewrite')), $errorOptions);
		else
			echo $this->Html->para($warningClasses, __d('me_cms', 'The %s module cannot be checked', $this->Html->strong('Rewrite')), $warningOptions);
		//Expires
		if(is_bool($apache['expires']) && $apache['expires'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s module is enabled', $this->Html->strong('Expires')), $successOptions);
		elseif(is_bool($apache['expires']) && !$apache['expires'])
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s module is not enabled', $this->Html->strong('Expires')), $errorOptions);
		else
			echo $this->Html->para($warningClasses, __d('me_cms', 'The %s module cannot be checked', $this->Html->strong('Expires')), $warningOptions);
				
		echo $this->Html->h4('PHP');
		//Current version
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s version: %s', $this->Html->strong('PHP'), $php['current_version']));
		//Check version
		if($php['check_version'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s version is at least %s', $this->Html->strong('PHP'), $this->Html->strong($php['required_version'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s version is less than %s', $this->Html->strong('PHP'), $this->Html->strong($php['required_version'])), $errorOptions);
		//Imagick
		if($php['imagick'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s extension is enabled', $this->Html->strong('Imagick')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s extension is not enabled', $this->Html->strong('Imagick')), $errorOptions);
		
		echo $this->Html->h4('ffmpegthumbnailer');
		if($ffmpegthumbnailer)
			echo $this->Html->para($successClasses, __d('me_cms', '%s is available', $this->Html->strong('ffmpegthumbnailer')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', '%s is not available', $this->Html->strong('ffmpegthumbnailer')), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Banners'));
		//`www` directory is writable
		if($banners['www_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($banners['www_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($banners['www_path'])), $errorOptions);
		//tmp directory is writable
		if($banners['tmp_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($banners['tmp_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($banners['tmp_path'])), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Photos'));
		//`www` directory is writable
		if($photos['www_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($photos['www_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($photos['www_path'])), $errorOptions);
		//tmp directory is writable
		if($photos['tmp_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($photos['tmp_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($photos['tmp_path'])), $errorOptions);	
		
		echo $this->Html->h4(__d('me_cms', 'Thumbs'));
		//Photos thumbs are writable
		if($thumbs['photos_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($thumbs['photos_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($thumbs['photos_path'])), $errorOptions);
		//Remotes thumbs are writable
		if($thumbs['remotes_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($thumbs['remotes_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($thumbs['remotes_path'])), $errorOptions);
		//Videos thumbs are writable
		if($thumbs['videos_writable'])
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($thumbs['videos_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($thumbs['videos_path'])), $errorOptions);
		
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
			echo $this->Html->para($successClasses, __d('me_cms', 'The directory %s is readable and writable', $this->Html->code($tmp['logs_path'])), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The directory %s is not readable or writable', $this->Html->code($tmp['logs_path'])), $errorOptions);
	?>
</div>