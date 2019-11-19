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
     * If `true`, a mock instance of the table will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Asserts that the table has a "belogs to" association
     * @param \Cake\ORM\Association $association Association
     * @return void
     */
    public function assertBelongsTo(Association $association)
    {
        $this->assertInstanceOf(BelongsTo::class, $association);
    }

    /**
     * Asserts that the table has a "belogs to many" association
     * @param \Cake\ORM\Association $association Association
     * @return void
     */
    public function assertBelongsToMany(Association $association)
    {
        $this->assertInstanceOf(BelongsToMany::class, $association);
    }

    /**
     * Asserts that the table has a behavior
     * @param string|array $behavior Behavior name as string or array
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
     * @param \Cake\ORM\Association $association Association
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

        //Automatically loads fixtures for some tests
        if (preg_match('/^test(Associations|EventMethods|Find|BuildRules|QueryFromFilter)/', $this->getName())
            && !$this->autoFixtures) {
            $this->loadFixtures();
        }

        if (!$this->Table && $this->autoInitializeClass) {
            $alias = substr(get_class_short_name($this), 0, -9);
            $className = 'MeCms\\Model\\Table\\' . $alias . 'Table';
            $this->Table = $this->getTable($alias, compact('className'));
        }
    }
}
