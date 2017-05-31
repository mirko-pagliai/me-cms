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
 * @since       2.16.1
 */
namespace MeCms\Controller\Traits;

/**
 * This trait provides a method to check if the latest search has been executed
 *  out of the minimum interval
 */
trait CheckLastSearchTrait
{
    /**
     * Checks if the latest search has been executed out of the minimum
     *  interval
     * @param string $queryId Query
     * @return bool
     */
    protected function checkLastSearch($queryId = false)
    {
        $interval = getConfig('security.search_interval');

        if (!$interval) {
            return true;
        }

        if ($queryId) {
            $queryId = md5($queryId);
        }

        $lastSearch = $this->request->session()->read('last_search');

        if ($lastSearch) {
            //Checks if it's the same search
            if ($queryId && !empty($lastSearch['id']) && $queryId === $lastSearch['id']) {
                return true;
            //Checks if the interval has not yet expired
            } elseif (($lastSearch['time'] + $interval) > time()) {
                return false;
            }
        }

        $this->request->session()->write('last_search', [
            'id' => $queryId,
            'time' => time(),
        ]);

        return true;
    }
}
