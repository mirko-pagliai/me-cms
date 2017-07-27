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

use MeCms\Model\Entity\BannersPosition;
use MeTools\TestSuite\TestCase;

/**
 * BannersPositionTest class
 */
class BannersPositionTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\BannersPosition
     */
    protected $BannersPosition;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->BannersPosition = new BannersPosition;
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->BannersPosition->isAccessible('id'));
        $this->assertFalse($this->BannersPosition->isAccessible('banner_count'));
        $this->assertFalse($this->BannersPosition->isAccessible('modified'));
    }
}
