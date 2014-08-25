<?php

$config = array('MeCmsBackend' => array(
	//Date formats
	'date' => array(
		//Long format
		'long'	=> '%Y/%m/%d',
		//Short format
		'short'	=> '%y/%m/%d',
	),
	//Datetime formats
	'datetime' => array(
		//Long format
		'long'	=> '%Y/%m/%d, %H:%M',
		//Short format
		'short'	=> '%y/%m/%d, %H:%M'
	),
	//Site options
	'site' => array(
		//Number of records to show per page
		'records_for_page' => 10,
		//Site title
		'title' => 'MeCms Backend'
	),
	//Users options
	'users' => array(
		//ID of users group by default
		'default_group' => 3
	)
));