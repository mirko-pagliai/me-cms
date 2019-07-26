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
namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\TestSuite\EntityTestCase;

/**
 * BannerTest class
 */
class BannerTest extends EntityTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Entity->set('filename', 'example.gif');
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity([]) or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'modified']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField(['path', 'www']);
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->assertEquals(BANNERS . 'example.gif', $this->Entity->get('path'));
    }

    /**
     * Test for `_getWww()` method
     * @test
     */
    public function testWwwGetMutator()
    {
        $this->assertEquals(BANNERS_WWW . 'example.gif', $this->Entity->get('www'));
    }
}
