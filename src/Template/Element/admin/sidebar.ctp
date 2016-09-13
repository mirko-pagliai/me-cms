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

//Renders menus for MeCms
echo $this->MenuBuilder->renderAsCollapse(MECMS);

//Renders menus for each plugin
foreach (\MeCms\Core\Plugin::all([
    'exclude' => [MECMS, METOOLS, 'Assets', 'DatabaseBackup', 'Thumbs'],
    ]) as $plugin) {
    $menus = $this->MenuBuilder->renderAsCollapse($plugin);

    if (!empty($menus)) {
        echo $this->Html->h6($plugin);
        echo $menus;
    }
}
