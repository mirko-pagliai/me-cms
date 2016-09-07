<?php
$baseDir = dirname(dirname(__FILE__));
return [
    'plugins' => [
        'Assets' => $baseDir . '/vendor/mirko-pagliai/assets/',
        'Bake' => $baseDir . '/vendor/cakephp/bake/',
        'DatabaseBackup' => $baseDir . '/vendor/mirko-pagliai/database-backup/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'MeCms' => $baseDir . '/vendor/mirko-pagliai/me-cms/',
        'MeTools' => $baseDir . '/vendor/mirko-pagliai/me-tools/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'Thumbs' => $baseDir . '/vendor/mirko-pagliai/thumbs/'
    ]
];