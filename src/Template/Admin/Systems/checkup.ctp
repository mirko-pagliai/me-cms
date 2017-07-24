<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'System checkup'));

//Sets some classes and options
$errorClasses = 'bg-danger text-danger padding10';
$infoClasses = 'bg-info text-info padding10';
$successClasses = 'bg-success text-success padding10';
$warningClasses = 'bg-warning text-warning padding10';
$errorOptions = ['icon' => 'times'];
$successOptions = ['icon' => 'check'];
$warningOptions = ['icon' => 'check'];

/* -------------------------------- */
/*			MeCms version			*/
/* -------------------------------- */
$text = __d('me_cms', '{0} version: {1}', $this->Html->strong(ME_CMS), $plugins['mecms']);
echo $this->Html->div('col-sm-12', $this->Html->para($infoClasses, $text));

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*			CakePHP version			*/
/* -------------------------------- */
$text = __d('me_cms', '{0} version: {1}', $this->Html->strong('CakePHP'), $plugins['cakephp']);
echo $this->Html->div('col-sm-12', $this->Html->para($infoClasses, $text));

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*			Cache status			*/
/* -------------------------------- */
if ($cache) {
    $class = $successClasses;
    $options = $successOptions;
    $text = __d('me_cms', 'The cache is enabled');
} else {
    $class = $errorClasses;
    $options = $errorOptions;
    $text = __d('me_cms', 'The cache is disabled or debugging is active');
}
echo $this->Html->div('col-sm-12', $this->Html->para($class, $text, $options));

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*				Plugins				*/
/* -------------------------------- */
echo $this->Html->h4(__d('me_cms', 'Plugins'));

//Plugins version
foreach ($plugins['plugins'] as $plugin => $version) {
    $text = __d('me_cms', '{0} version: {1}', $this->Html->strong($plugin), $version);
    echo $this->Html->div('col-sm-6', $this->Html->para($infoClasses, $text));
}

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*				Apache				*/
/* -------------------------------- */
echo $this->Html->h4('Apache');
//Current version
$text = __d('me_cms', '{0} version: {1}', $this->Html->strong('Apache'), $apache['version']);
echo $this->Html->div('col-sm-12', $this->Html->para($infoClasses, $text));

//Apache's modules
foreach (['rewrite', 'expires'] as $mod) {
    if (is_bool($apache[$mod]) && $apache[$mod]) {
        $class = $successClasses;
        $options = $successOptions;
        $text = __d('me_cms', 'The {0} module is enabled', $this->Html->strong($mod));
    } elseif (is_bool($apache[$mod]) && !$apache[$mod]) {
        $class = $errorClasses;
        $options = $errorOptions;
        $text = __d('me_cms', 'The {0} module is not enabled', $this->Html->strong($mod));
    } else {
        $class = $warningClasses;
        $options = $warningOptions;
        $text = __d('me_cms', 'The {0} module cannot be checked', $this->Html->strong($mod));
    }

    echo $this->Html->div('col-sm-6', $this->Html->para($class, $text, $options));
}

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*				PHP					*/
/* -------------------------------- */
echo $this->Html->h4('PHP');
//Current version
$text = __d('me_cms', '{0} version: {1}', $this->Html->strong('PHP'), PHP_VERSION);
echo $this->Html->div('col-sm-12', $this->Html->para($text));

//PHP's extensions
foreach ($phpExtensions as $extension => $exists) {
    if ($exists) {
        $class = $successClasses;
        $options = $successOptions;
        $text = __d('me_cms', 'The {0} extension is enabled', $this->Html->strong($extension));
    } else {
        $class = $errorClasses;
        $options = $errorOptions;
        $text = __d('me_cms', 'The {0} extension is not enabled', $this->Html->strong($extension));
    }

    echo $this->Html->div('col-sm-6', $this->Html->para($class, $text, $options));
}

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*				Backups				*/
/* -------------------------------- */
echo $this->Html->h4(__d('me_cms', 'Backups'));

if ($backups['writeable']) {
    $class = $successClasses;
    $options = $successOptions;
    $text = __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($backups['path']));
} else {
    $class = $errorClasses;
    $options = $errorOptions;
    $text = __d('me_tools', 'File or directory {0} not writeable', $this->Html->code($backups['path']));
}
echo $this->Html->div('col-sm-6', $this->Html->para($class, $text, $options));

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*				Webroot				*/
/* -------------------------------- */
echo $this->Html->h4(__d('me_cms', 'Webroot'));

//Webroot directories
foreach ($webroot as $dir) {
    if ($dir['writeable']) {
        $class = $successClasses;
        $options = $successOptions;
        $text = __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($dir['path']));
    } else {
        $class = $errorClasses;
        $options = $errorOptions;
        $text = __d('me_tools', 'File or directory {0} not writeable', $this->Html->code($dir['path']));
    }

    echo $this->Html->div('col-sm-6', $this->Html->para($class, $text, $options));
}

echo $this->Html->div('clearfix');

/* -------------------------------- */
/*			Temporary				*/
/* -------------------------------- */
echo $this->Html->h4(__d('me_cms', 'Temporary directories'));

//Temporary directories
foreach ($temporary as $dir) {
    if ($dir['writeable']) {
        $class = $successClasses;
        $options = $successOptions;
        $text = __d('me_cms', 'The directory {0} is readable and writable', $this->Html->code($dir['path']));
    } else {
        $class = $errorClasses;
        $options = $errorOptions;
        $text = __d('me_tools', 'File or directory {0} not writeable', $this->Html->code($dir['path']));
    }

    echo $this->Html->div('col-sm-6', $this->Html->para($class, $text, $options));
}
