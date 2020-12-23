<?php
declare(strict_types=1);

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
     * @return string|null Timestamp
     */
    public function getNextToBePublished(): ?string
    {
        return Cache::read('next_to_be_published', $this->getCacheName()) ?: null;
    }

    /**
     * Sets to cache the timestamp of the next record to be published.
     * This value can be used to check if the cache is valid
     * @return string|null Timestamp
     * @uses $cache
     */
    public function setNextToBePublished(): ?string
    {
        $next = $this->find()
            ->where([
                sprintf('%s.active', $this->getAlias()) => true,
                sprintf('%s.created >', $this->getAlias()) => new Time(),
            ])
            ->orderAsc(sprintf('%s.created', $this->getAlias()))
            ->extract('created')
            ->first();

        $next = $next ? $next->toUnixString() : null;

        Cache::write('next_to_be_published', $next, $this->getCacheName());

        return $next;
    }
}
