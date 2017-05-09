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
namespace MeCms\Controller\Traits;

use Cake\I18n\Time;

/**
 * This trait provides a method to fets start and end date as `Time` instances
 *  starting from a string. These can be used for a `where` condition to search
 *  for records based on a date.
 */
trait GetStartAndEndDateTrait
{
    /**
     * Gets start and end date as `Time` instances starting from a string.
     * These can be used for a `where` condition to search for records based on
     *  a date.
     * @param string $date Date as `today`, `yesterday`, `YYYY/MM/dd`,
     *  `YYYY/MM` or `YYYY`
     * @return array Array with start and end date as `Time` instances
     */
    protected function getStartAndEndDate($date)
    {
        $year = $month = $day = null;

        //Sets the start date
        if (in_array($date, ['today', 'yesterday'])) {
            $start = Time::parse($date);
        } else {
            list($year, $month, $day) = array_replace([null, null, null], explode('/', $date));

            $start = Time::now()->setDate($year, $month ?: 1, $day ?: 1);
        }

        $start = $start->setTime(0, 0, 0);

        //Sets the end date
        $end = Time::parse($start);

        if (($year && $month && $day) || in_array($date, ['today', 'yesterday'])) {
            $end = $end->addDay(1);
        } elseif ($year && $month) {
            $end = $end->addMonth(1);
        } else {
            $end = $end->addYear(1);
        }

        return [$start, $end];
    }
}
