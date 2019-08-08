<?php
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
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase as BaseTestCase;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * TestCase class
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var \Cake\ORM\Table
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
    public function tearDown()
    {
        parent::tearDown();

        //Clears all cache keys
        if ($this->Table && !$this->cacheToClear) {
            $this->cacheToClear = $this->Table->getCacheName(true);
        }

        foreach ($this->cacheToClear as $cacheKey) {
            Cache::getConfig($cacheKey) ?: $this->fail('Cache key `' . $cacheKey . '` does not exist');
            Cache::clear(false, $cacheKey);
        }

        try {
            unlink_recursive(KCFINDER, 'empty');
            unlink_recursive(WWW_ROOT . 'vendor', 'empty');
        } catch (IOException $e) {
        }
    }

    /**
     * Get a table instance from the registry
     * @param string $alias The alias name you want to get
     * @param array $options The options you want to build the table with
     * @return \Cake\ORM\Table|null
     */
    protected function getTable($alias, array $options = [])
    {
        if ($alias === 'App' || (isset($options['className']) && !class_exists($options['className']))) {
            return null;
        }

        TableRegistry::getTableLocator()->clear();

        return TableRegistry::getTableLocator()->get($alias, $options);
    }
}
