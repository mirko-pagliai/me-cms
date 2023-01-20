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
 * @since       2.27.1
 */

namespace MeCms\ORM;

use Cake\Datasource\QueryTrait;
use Cake\ORM\Query as BaseQuery;
use RuntimeException;

/**
 * Contains the characteristics for an object that is attached to a repository and can retrieve results based on any criteria
 */
class Query extends BaseQuery
{
    use QueryTrait {
        QueryTrait::cache as protected _cache;
        QueryTrait::__call as private _call;
    }

    /**
     * Enable result caching for this query.
     *
     * Unlike the method provided by CakePHP, it uses the `getCacheName()` table method to get the default name of the
     *  cache config to use, if that method exists.
     * @param \Closure|string|false $key Either the cache key or a function to generate the cache key. When using a
     *  function, this query instance will be supplied as an argument
     * @param string|\Cake\Cache\CacheEngine $config Either the name of the cache config to use, or a cache config instance
     * @return $this
     * @throws \RuntimeException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function cache($key, $config = '')
    {
        if ($this->_type !== 'select' && $this->_type !== null) {
            throw new RuntimeException('You cannot cache the results of non-select queries.');
        }

        if (!$config && method_exists($this->getRepository(), 'getCacheName')) {
            /** @var \MeCms\Model\Table\AppTable $table */
            $table = $this->getRepository();
            $config = $table->getCacheName();
        }

        return $this->_cache($key, $config ?: 'default');
    }
}
