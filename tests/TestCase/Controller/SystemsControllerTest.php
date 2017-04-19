<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\TestSuite\IntegrationTestCase;

/**
 * SystemsControllerTest class
 */
class SystemsControllerTest extends IntegrationTestCase
{
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
        'plugin.me_cms.photos',
        'plugin.me_cms.photos_albums',
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
    ];

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        $controller->Cookie->config('key', 'somerandomhaskeysomerandomhaskey');

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `acceptCookies()` method
     * @test
     */
    public function testAcceptCookies()
    {
        $this->get(['_name' => 'acceptCookies']);
        $this->assertRedirect(['_name' => 'homepage']);

        $expire = Time::createFromTimestamp($this->_response->cookie('cookies-policy')['expire']);
        $this->assertCookie(true, 'cookies-policy');
        $this->assertTrue($expire->isWithinNext('999 days'));
    }

    /**
     * Tests for `contactUs()` method
     * @test
     */
    public function testContactUs()
    {
        $url = ['_name' => 'contactUs'];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Systems/contact_us.ctp');

        $contactFromView = $this->viewVariable('contact');
        $this->assertInstanceof('MeCms\Form\ContactUsForm', $contactFromView);

        //Disabled
        Configure::write('MeCms.default.contact_us', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertSession('Disabled', 'Flash.flash.0.message');
    }

    /**
     * Tests for `ipNotAllowed()` method
     * @test
     */
    public function testIpNotAllowed()
    {
        $this->get(['_name' => 'ipNotAllowed']);
        $this->assertRedirect(['_name' => 'homepage']);
    }

    /**
     * Tests for `offline()` method
     * @test
     */
    public function testOffline()
    {
        $this->get(['_name' => 'offline']);
        $this->assertRedirect(['_name' => 'homepage']);

        //Offline
        Configure::write('MeCms.default.offline', true);
        $this->get(['_name' => 'offline']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Systems/offline.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');
    }

    /**
     * Tests for `sitemap()` method
     * @test
     */
    public function testSitemap()
    {
        $this->loadFixtures(
            'Pages',
            'PagesCategories',
            'Photos',
            'PhotosAlbums',
            'Posts',
            'PostsCategories'
        );

        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();

        $this->assertContentType('application/x-gzip');
        $this->assertFileResponse(SITEMAP);

        //@codingStandardsIgnoreLine
        @unlink(SITEMAP);
    }
}
