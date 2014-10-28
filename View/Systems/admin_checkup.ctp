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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Systems
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('systems', 'nav')); ?>

<div class="systems index">
	<?php 
		echo $this->Html->h2(__d('me_cms', 'System checkup'));
	
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s version: %s', $this->Html->strong('MeCMS'), $version));
		echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s version: %s', $this->Html->strong('CakePHP'), $cakeVersion));
		
		
		echo $this->Html->h4(__d('me_cms', 'Plugins'));
		foreach($plugins as $plugin)
			echo $this->Html->para('bg-info text-info padding10', __d('me_cms', '%s plugin version: %s', $this->Html->strong($plugin['name']), $plugin['version']));
		
		$successClasses = 'bg-success text-success padding10';
		$errorClasses = 'bg-danger text-danger padding10';
		$successOptions = array('icon' => 'check-circle');
		$errorOptions = array('icon' => 'times-circle');

		echo $this->Html->h4('Apache');
		if($rewrite)
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s module is enabled', $this->Html->strong('Rewrite')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s module is not enabled', $this->Html->strong('Rewrite')), $errorOptions);

		if($expires)
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s module is enabled', $this->Html->strong('Expires')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s module is not enabled', $this->Html->strong('Expires')), $errorOptions);
		
		echo $this->Html->h4('PHP');
		if($phpVersion)
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s version is at least %s', $this->Html->strong('PHP'), $this->Html->strong($phpRequired)), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s version is less than %s', $this->Html->strong('PHP'), $this->Html->strong($phpRequired)), $errorOptions);

		if($imagick)
			echo $this->Html->para($successClasses, __d('me_cms', 'The %s extension is enabled', $this->Html->strong('Imagick')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The %s extension is not enabled', $this->Html->strong('Imagick')), $errorOptions);
		
		echo $this->Html->h4('ffmpegthumbnailer');
		if($ffmpegthumbnailer)
			echo $this->Html->para($successClasses, __d('me_cms', '%s is available', $this->Html->strong('ffmpegthumbnailer')), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', '%s is not available', $this->Html->strong('ffmpegthumbnailer')), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Banners'));
		if($bannersWWW)
			echo $this->Html->para($successClasses, __d('me_cms', 'The webroot banners directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The webroot banners directory is not readable or writable'), $errorOptions);
		if($bannersTmp)
			echo $this->Html->para($successClasses, __d('me_cms', 'The temporary banners directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The temporary banners directory is not readable or writable'), $errorOptions);
		
		echo $this->Html->h4(__d('me_cms', 'Photos'));
		if($photosWWW)
			echo $this->Html->para($successClasses, __d('me_cms', 'The webroot photos directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The webroot photos directory is not readable or writable'), $errorOptions);
		if($photosTmp)
			echo $this->Html->para($successClasses, __d('me_cms', 'The temporary photos directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The temporary photos directory is not readable or writable'), $errorOptions);
			
		
		echo $this->Html->h4(__d('me_cms', 'Temporary directories'));
		if($tmp)
			echo $this->Html->para($successClasses, __d('me_cms', 'The temporary directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The temporary directory is not readable or writable'), $errorOptions);

		if($cacheStatus)
			echo $this->Html->para($successClasses, __d('me_cms', 'The cache is enabled'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The cache is disabled or debugging is active'), $errorOptions);

		if($cache)
			echo $this->Html->para($successClasses, __d('me_cms', 'The cache is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The cache is not readable or writable'), $errorOptions);
		
		if($logs)
			echo $this->Html->para($successClasses, __d('me_cms', 'The logs directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The logs directory is not readable or writable'), $errorOptions);
		
		if($thumbs)
			echo $this->Html->para($successClasses, __d('me_cms', 'The thumbnail directory is readable and writable'), $successOptions);
		else
			echo $this->Html->para($errorClasses, __d('me_cms', 'The thumbnail directory is not readable or writable'), $errorOptions);
	?>
</div>