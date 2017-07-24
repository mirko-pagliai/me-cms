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

if (!defined('ME_CMS_CACHE')) {
    define('ME_CMS_CACHE', CACHE . 'me_cms' . DS);
}

//Default options (with File engine)
$options = [
    'className' => 'File',
    'duration' => '+999 days',
    'path' => ME_CMS_CACHE,
    'prefix' => '',
    'mask' => 0777,
];

return ['Cache' => [
    //Default and admin configurations
    'default' => am($options, ['path' => ME_CMS_CACHE . 'default']),
    'admin' => am($options, ['path' => ME_CMS_CACHE . 'admin']),

    //Groups
    'banners' => am($options, ['path' => ME_CMS_CACHE . 'banners']),
    'pages' => am($options, ['path' => ME_CMS_CACHE . 'pages']),
    'photos' => am($options, ['path' => ME_CMS_CACHE . 'photos']),
    'posts' => am($options, ['path' => ME_CMS_CACHE . 'posts']),
    'static_pages' => am($options, ['path' => ME_CMS_CACHE . 'static_pages']),
    'users' => am($options, ['path' => ME_CMS_CACHE . 'users']),
]];
