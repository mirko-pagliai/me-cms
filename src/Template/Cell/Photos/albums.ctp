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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */

if (empty($albums) || count($albums) < 2) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Albums'));

echo $this->Form->create(false, [
    'type' => 'get',
    'url' => ['_name' => 'album', 'album'],
]);
echo $this->Form->input('q', [
    'label' => false,
    'onchange' => 'send_form(this)',
    'options' => array_map(function ($album) {
        return sprintf('%s (%d)', $album->title, $album->photo_count);
    }, $albums),
]);
echo $this->Form->end();
