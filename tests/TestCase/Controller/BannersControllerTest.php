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
namespace MeCms\Test\TestCase\Controller;

use MeCms\TestSuite\ControllerTestCase;

/**
 * BannersControllerTest class
 */
class BannersControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Banners',
    ];

    /**
     * Tests for `open()` method
     * @test
     */
    public function testOpen()
    {
        $banner = $this->Table->find('active')->first();
        $this->get(['_name' => 'banner', $banner->id]);
        $this->assertRedirect($banner->target);

        //Checks the `click_count` has been incremented
        $this->assertEquals(++$banner->click_count, $this->Table->findById(1)->extract('click_count')->first());
    }
}
