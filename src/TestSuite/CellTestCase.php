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
 * @since       2.25.4
 */
namespace MeCms\TestSuite;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\TestCase;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;

/**
 * Abstract class for test entities
 */
abstract class CellTestCase extends TestCase
{
    /**
     * @var \Cake\ORM\Table
     */
    protected $Table;

    /**
     * Entity instance
     * @var \MeCms\View\Helper\WidgetHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Widget;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Cache keys to clear for each test
     * @var array
     */
    protected $cacheToClear = [];

    /**
     * Called before every test method
     * @return void
     * @uses $Table
     * @uses $Widget
     * @uses $autoInitializeClass
     * @uses $cacheToClear
     */
    public function setUp()
    {
        parent::setUp();

        if (!$this->Widget && $this->autoInitializeClass) {
            $this->Widget = $this->getMockBuilder(WidgetHelper::class)
                ->setMethods(null)
                ->setConstructorArgs([new View()])
                ->getMock();
        }

        if (!$this->Table && $this->autoInitializeClass) {
            $parts = explode('\\', get_class($this));
            $last = array_pop($parts);
            $alias = substr($last, 0, strlen($last) - 15);

            //Tries to retrieve the table
            $className = sprintf('%s\\Model\\Table\\%sTable', $parts[0], $alias);
            if (class_exists($className) && $alias !== 'App') {
                $this->Table = TableRegistry::getTableLocator()->get('MeCms.' . $alias);
            }
        }

        if ($this->Table && !$this->cacheToClear) {
            $this->cacheToClear = $this->Table->getCacheName(true);
        }

        //Clears all cache keys
        foreach ($this->cacheToClear as $cacheKey) {
            Cache::getConfig($cacheKey) ?: $this->fail('Cache key `' . $cacheKey . '` does not exist');
            Cache::clear(false, $cacheKey);
        }
    }
}
