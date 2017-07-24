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

use Cake\TestSuite\TestCase;
use MeCms\Model\Entity\Page;

/**
 * PageTest class
 */
class PageTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\Page
     */
    protected $Page;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Page = new Page;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Page);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('MeCms\Model\Entity\Page', $this->Page);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->Page->isAccessible('id'));
        $this->assertFalse($this->Page->isAccessible('preview'));
        $this->assertFalse($this->Page->isAccessible('modified'));
    }
}
