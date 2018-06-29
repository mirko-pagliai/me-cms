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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Controller\Admin\BannersPositionsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * BannersPositionsControllerTest class
 */
class BannersPositionsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\BannersPositionsTable
     */
    protected $BannersPositions;

    /**
     * @var \MeCms\Controller\Admin\BannersPositionsController
     */
    protected $Controller;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.banners_positions',
    ];

    /**
     * @var array
     */
    protected $url;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUserGroup('admin');

        $this->BannersPositions = TableRegistry::get(ME_CMS . '.BannersPositions');

        $this->Controller = new BannersPositionsController;

        Cache::clear(false, $this->BannersPositions->cache);

        $this->url = ['controller' => 'BannersPositions', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/BannersPositions/index.ctp');

        $positionsFromView = $this->viewVariable('positions');
        $this->assertNotEmpty($positionsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionsFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/BannersPositions/add.ctp');

        $positionFromView = $this->viewVariable('position');
        $this->assertNotEmpty($positionFromView);
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);

        //POST request. Data are valid
        $this->post($url, ['title' => 'new-position-title', 'descriptions' => 'position description']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $positionFromView = $this->viewVariable('position');
        $this->assertNotEmpty($positionFromView);
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/BannersPositions/edit.ctp');

        $positionFromView = $this->viewVariable('position');
        $this->assertNotEmpty($positionFromView);
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another-title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $positionFromView = $this->viewVariable('position');
        $this->assertNotEmpty($positionFromView);
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $id = $this->BannersPositions->find()->where(['banner_count <' => 1])->extract('id')->first();

        //POST request. This position has no banner
        $this->post($this->url + ['action' => 'delete', $id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        $id = $this->BannersPositions->find()->where(['banner_count >=' => 1])->extract('id')->first();

        //POST request. This position has some banners, so it cannot be deleted
        $this->post($this->url + ['action' => 'delete', $id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
    }
}
