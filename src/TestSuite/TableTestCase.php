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
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use MeCms\TestSuite\TestCase;

/**
 * Abstract class for test tables
 */
abstract class TableTestCase extends TestCase
{
    /**
     * Table instance
     * @var \Cake\ORM\Table|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Table;

    /**
     * If `true`, a mock instance of the table will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Cache keys to clear for each test
     * @var array
     */
    protected $cacheToClear = [];

    /**
     * Asserts that the table has a "belogs to" association
     * @param Association $association Association
     * @return void
     */
    public function assertBelongsTo(Association $association)
    {
        $this->assertInstanceOf(BelongsTo::class, $association);
    }

    /**
     * Asserts that the table has a "belogs to many" association
     * @param Association $association Association
     * @return void
     */
    public function assertBelongsToMany(Association $association)
    {
        $this->assertInstanceOf(BelongsToMany::class, $association);
    }

    /**
     * Asserts that the table has a behavior
     * @param string $behavior Behavior name
     * @return void
     * @uses $Table
     */
    public function assertHasBehavior($behavior)
    {
        $this->Table ?: $this->fail('The property `$this->Table` has not been set');

        foreach ((array)$behavior as $name) {
            $this->assertTrue($this->Table->hasBehavior($name));
        }
    }

    /**
     * Asserts that the table has a "many" association
     * @param Association $association Association
     * @return void
     */
    public function assertHasMany(Association $association)
    {
        $this->assertInstanceOf(HasMany::class, $association);
    }

    /**
     * Called before every test method
     * @return void
     * @uses $Table
     * @uses $autoInitializeClass
     * @uses $cacheToClear
     */
    public function setUp()
    {
        parent::setUp();

        if (!$this->Table && $this->autoInitializeClass) {
            $parts = explode('\\', get_class($this));
            array_splice($parts, 1, 2, []);
            $parts[] = substr(array_pop($parts), 0, -4);
            $className = implode('\\', $parts);

            if (class_exists($className)) {
                $alias = substr(array_pop($parts), 0, -5);
                $this->Table = $this->getMockForModel($alias, null, compact('className'));

                //Tries to retrieve all cache names related to this table and associated tables
                if (method_exists($this->Table, 'getCacheName')) {
                    $this->cacheToClear = array_merge($this->cacheToClear, $this->Table->getCacheName(true));
                }
            }
        }

        //Clears all cache keys
        foreach ($this->cacheToClear as $cacheKey) {
            Cache::getConfig($cacheKey) ?: $this->fail('Cache key `' . $cacheKey . '` does not exist');
            Cache::clear(false, $cacheKey);
        }
    }
}
