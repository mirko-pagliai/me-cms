<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

use MeCms\Model\Entity\PostsCategory;
use MeCms\TestSuite\Admin\ControllerTestCase;

/**
 * PostsCategoriesControllerTest class
 * @group admin-controller
 */
class PostsCategoriesControllerTest extends ControllerTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.PostsCategories',
    ];

    /**
     * Tests for `beforeFilter()` method
     * @uses \MeCms\Controller\Admin\PostsCategoriesController::beforeFilter()
     * @test
     */
    public function testBeforeFilter(): void
    {
        foreach (['add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertNotEmpty($this->viewVariable('categories'));
        }
    }

    /**
     * Tests for `index()` method
     * @uses \MeCms\Controller\Admin\PostsCategoriesController::index()
     * @test
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'PostsCategories' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(PostsCategory::class, $this->viewVariable('categories'));
    }

    /**
     * Tests for `add()` method
     * @uses \MeCms\Controller\Admin\PostsCategoriesController::add()
     * @test
     */
    public function testAdd(): void
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'PostsCategories' . DS . 'form.php');
        $this->assertInstanceOf(PostsCategory::class, $this->viewVariable('category'));

        //POST request. Data are valid
        $this->post($url, ['title' => 'new category', 'slug' => 'category-slug']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(PostsCategory::class, $this->viewVariable('category'));
    }

    /**
     * Tests for `edit()` method
     * @uses \MeCms\Controller\Admin\PostsCategoriesController::edit()
     * @test
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'PostsCategories' . DS . 'form.php');
        $this->assertInstanceOf(PostsCategory::class, $this->viewVariable('category'));

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(PostsCategory::class, $this->viewVariable('category'));
    }

    /**
     * Tests for `delete()` method
     * @uses \MeCms\Controller\Admin\PostsCategoriesController::delete()
     * @test
     */
    public function testDelete(): void
    {
        //POST request. This category has no pages
        $this->post($this->url + ['action' => 'delete', 2]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->all()->isEmpty());

        //POST request. This category has some pages, so it cannot be deleted
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
        $this->assertFalse($this->Table->findById(1)->all()->isEmpty());
    }
}
