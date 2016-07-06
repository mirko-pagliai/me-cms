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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */

if(!defined('ME_CMS_CACHE')) {
    define('ME_CMS_CACHE', CACHE.'me_cms'.DS);
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
	//Default and backend configurations
	'default' => am($options, ['path' => ME_CMS_CACHE.'default']),
	'backend' => am($options, ['path' => ME_CMS_CACHE.'backend']),
	
	//Groups
	'banners' => am($options, ['path' => ME_CMS_CACHE.'banners']),
	'pages' => am($options, ['path' => ME_CMS_CACHE.'pages']),
	'photos' => am($options, ['path' => ME_CMS_CACHE.'photos']),
	'posts' => am($options, ['path' => ME_CMS_CACHE.'posts']),
	'users' => am($options, ['path' => ME_CMS_CACHE.'users']),
]];