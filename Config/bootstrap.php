<?php

$duration = '+999 days';
$engine = 'File';
$prefix = 'mecms_';

Cache::config('pages', array(
    'engine' => $engine,
    'duration' => $duration,
    'prefix' => $prefix,
	'groups' => array('pages')
));

Cache::config('posts', array(
    'engine' => $engine,
    'duration' => $duration,
    'prefix' => $prefix,
	'groups' => array('posts')
));