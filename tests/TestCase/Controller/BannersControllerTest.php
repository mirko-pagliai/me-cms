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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * BannersControllerTest class
 */
class BannersControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\BannersTable
     */
    protected $Banners;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.banners',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Banners = TableRegistry::get(ME_CMS . '.Banners');

        Cache::clear(false, $this->Banners->cache);
    }

    /**
     * Tests for `open()` method
     * @test
     */
    public function testOpen()
    {
        $banner = $this->Banners->find('active')->first();

        $this->get(['_name' => 'banner', $banner->id]);
        $this->assertRedirect($banner->target);

        //Checks the `click_count` has been incremented
        $this->assertEquals(++$banner->click_count, $this->Banners->findById(1)->extract('click_count')->first());
    }
}
