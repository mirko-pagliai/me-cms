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
 * @since       2.25.4
 */

namespace MeCms\TestSuite;

use MeCms\TestSuite\TestCase;

/**
 * Abstract class for test entities
 */
abstract class EntityTestCase extends TestCase
{
    /**
     * Entity instance
     * @var \Cake\ORM\Entity&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $Entity;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Asserts that the entity has a "no accessible" property
     * @param string|array $property Property name
     * @return void
     * @uses $Entity
     */
    public function assertHasNoAccessibleProperty($property): void
    {
        $this->Entity ?: $this->fail('The property `$this->Entity` has not been set');

        foreach ((array)$property as $name) {
            $this->assertFalse($this->Entity->isAccessible($name));
        }
    }

    /**
     * Called before every test method
     * @return void
     * @uses $Entity
     * @uses $autoInitializeClass
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!$this->Entity && $this->autoInitializeClass) {
            /** @var class-string<\Cake\ORM\Entity> $className */
            $className = $this->getOriginClassNameOrFail($this);
            $this->Entity = $this->getMockBuilder($className)
                ->setMethods(null)
                ->getMock();
        }
    }
}
