<?php
	foreach($config['topbar'] as $name) {
		list($plugin, $name) = pluginSplit($name);

		$helper = empty($plugin) ? 'Menu' : sprintf('%sMenu', $plugin);

		echo $this->$helper->get($name, 'collapse');
	}							
?>