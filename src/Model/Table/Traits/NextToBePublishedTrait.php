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
namespace MeCms\Model\Table\Traits;

use Cake\Cache\Cache;
use Cake\I18n\Time;

/**
 * This trait provides methods to handle the next record to be published
 */
trait NextToBePublishedTrait
{
    /**
     * Gets from cache the timestamp of the next record to be published.
     * This value can be used to check if the cache is valid
     * @return string|bool Timestamp or `false`
     */
    public function getNextToBePublished()
    {
        return Cache::read('next_to_be_published', $this->cache);
    }

    /**
     * Sets to cache the timestamp of the next record to be published.
     * This value can be used to check if the cache is valid
     * @return string|bool Timestamp or `false`
     * @uses $cache
     */
    public function setNextToBePublished()
    {
        $next = $this->find()
            ->where([
                sprintf('%s.active', $this->getAlias()) => true,
                sprintf('%s.created >', $this->getAlias()) => new Time,
            ])
            ->order([sprintf('%s.created', $this->getAlias()) => 'ASC'])
            ->extract('created')
            ->first();

        $next = empty($next) ? false : $next->toUnixString();

        Cache::write('next_to_be_published', $next, $this->cache);

        return $next;
    }
}
