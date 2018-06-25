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

use MeCms\Controller\Admin\PostsTagsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PhotosControllerTest class
 */
class PostsTagsControllerTest extends IntegrationTestCase
{
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
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PostsTags/index.ctp');

        $tagsFromView = $this->viewVariable('tags');
        $this->assertNotEmpty($tagsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagsFromView);
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
        $this->assertTemplate(ROOT . 'src/Template/Admin/PostsTags/edit.ctp');

        $tagFromView = $this->viewVariable('tag');
        $this->assertNotEmpty($tagFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagFromView);

        //POST request. Data are valid
        $this->post($url, ['tag' => 'another tag']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['tag' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $tagFromView = $this->viewVariable('tag');
        $this->assertNotEmpty($tagFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagFromView);
    }
}
