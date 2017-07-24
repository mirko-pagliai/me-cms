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

use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\PostsTagsController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * PhotosControllerTest class
 */
class PostsTagsControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\PostsTagsController
     */
    protected $Controller;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
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

        $this->Controller = new PostsTagsController;

        $this->url = ['controller' => 'PostsTags', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller);
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => true,
        ]);

        //`edit` action
        $this->Controller = new PostsTagsController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'edit');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PostsTags/index.ctp');

        $tagsFromView = $this->viewVariable('tags');
        $this->assertInstanceof('Cake\ORM\ResultSet', $tagsFromView);
        $this->assertNotEmpty($tagsFromView);

        foreach ($tagsFromView as $tag) {
            $this->assertInstanceof('MeCms\Model\Entity\Tag', $tag);
        }
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PostsTags/edit.ctp');

        $tagFromView = $this->viewVariable('tag');
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagFromView);
        $this->assertNotEmpty($tagFromView);

        //POST request. Data are valid
        $this->post($url, ['tag' => 'another tag']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['tag' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $tagFromView = $this->viewVariable('tag');
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagFromView);
        $this->assertNotEmpty($tagFromView);
    }
}
