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

namespace MeCms\Test\TestCase\Controller\Admin;

use MeCms\Model\Entity\Tag;
use MeCms\TestSuite\Admin\ControllerTestCase;

/**
 * PostsTagsControllerTest class
 * @group admin-controller
 */
class PostsTagsControllerTest extends ControllerTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
    ];

    /**
     * Tests for `isAuthorized()` method
     * @uses \MeCms\Controller\Admin\PostsTagsController::isAuthorized()
     * @test
     */
    public function testIsAuthorized(): void
    {
        $this->assertAllGroupsAreAuthorized('index');

        parent::testIsAuthorized();
    }

    /**
     * Tests for `index()` method
     * @uses \MeCms\Controller\Admin\PostsTagsController::index()
     * @test
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'PostsTags' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(Tag::class, $this->viewVariable('tags'));
    }

    /**
     * Tests for `edit()` method
     * @uses \MeCms\Controller\Admin\PostsTagsController::edit()
     * @test
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'PostsTags' . DS . 'edit.php');
        $this->assertInstanceOf(Tag::class, $this->viewVariable('tag'));

        //POST request. Data are valid
        $this->post($url, ['tag' => 'another tag']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['tag' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(Tag::class, $this->viewVariable('tag'));
    }
}
