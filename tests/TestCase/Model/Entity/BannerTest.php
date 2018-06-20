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
 */
namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\Model\Entity\Banner;
use MeTools\TestSuite\TestCase;

/**
 * BannerTest class
 */
class BannerTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\Banner
     */
    protected $Banner;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Banner = new Banner;
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->Banner->isAccessible('id'));
        $this->assertFalse($this->Banner->isAccessible('modified'));
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['path', 'www'], $this->Banner->getVirtual());
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $this->Banner->filename = 'example.gif';
        $this->assertEquals(BANNERS . 'example.gif', $this->Banner->path);
    }

    /**
     * Test for `_getWww()` method
     * @test
     */
    public function testWwwGetMutator()
    {
        $this->Banner->filename = 'example.gif';
        $this->assertEquals(BANNERS_WWW . 'example.gif', $this->Banner->www);
    }
}
