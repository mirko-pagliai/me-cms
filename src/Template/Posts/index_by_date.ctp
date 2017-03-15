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
use Cake\I18n\Time;

$this->extend('/Posts/index');

$date = new Time;
$date->year($year)->month(empty($month) ? 1 : $month)->day(empty($day) ? 1 : $day);

if ($year && $month && $day) {
    if ($date->isToday()) {
        $title = __d('me_cms', 'Posts of today');
    } elseif ($date->isYesterday()) {
        $title = __d('me_cms', 'Posts of yesterday');
    } else {
        $title = __dx(
            'me_cms',
            'posts of day',
            'Posts of {0}',
            $date->i18nFormat(config('main.date.long'))
        );
    }
} elseif ($year && $month) {
    $title = __dx('me_cms', 'posts of month', 'Posts of {0}', $date->i18nFormat('MMMM y'));
} else {
    $title = __dx('me_cms', 'posts of year', 'Posts of {0}', $date->i18nFormat('y'));
}

$this->assign('title', $title);
$this->Breadcrumbs->add($title);
