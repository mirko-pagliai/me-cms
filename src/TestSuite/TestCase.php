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
 * @since       2.26.0
 */

namespace MeCms\TestSuite;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use MeTools\TestSuite\TestCase as BaseTestCase;

/**
 * TestCase class
 * @method \MeCms\Model\Table\AppTable getTable(string $alias, array $options = [])
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var \MeCms\Model\Table\AppTable
     */
    protected $Table;

    /**
     * Cache keys to clear for each test
     * @var array
     */
    protected $cacheToClear = [];

    /**
     * Called after every test method
     * @return void
     * @uses $Table
     * @uses $cacheToClear
     */
    public function tearDown(): void
    {
        parent::tearDown();

        //Clears all cache keys
        if ($this->Table && method_exists($this->Table, 'getCacheName')) {
            $this->cacheToClear = array_merge($this->cacheToClear, (array)$this->Table->getCacheName(true));
        }

        foreach ($this->cacheToClear as $cacheKey) {
            Cache::getConfig($cacheKey) ?: $this->fail('Cache key `' . $cacheKey . '` does not exist');
            Cache::clear($cacheKey);
        }
    }

    /**
     * Checks if the current `test` scheme is `mysql`
     * @return bool
     * @since 2.30.3
     */
    protected function isMySql(): bool
    {
        return ConnectionManager::get('test')->config()['scheme'] == 'mysql';
    }

    /**
     * Skips the test if the CakePHP version is less than the one being compared
     * @param string $version Version of CakePHP to compare
     * @param string $message The message to display
     * @return bool
     * @since 2.30.4-RC1
     */
    protected function skipIfCakeIsLessThan(string $version, string $message = ''): bool
    {
        return $this->skipIf(version_compare(Configure::version(), $version, '<'), $message);
    }
}
