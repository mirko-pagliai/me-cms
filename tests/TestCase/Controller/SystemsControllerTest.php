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
use MeCms\Controller\SystemsController;

/**
 * SystemsControllerTest class
 */
class SystemsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\SystemsController
     */
    protected $Controller;

    /**
     * Does not automatically load fixtures
     * @var bool
     */
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
        $controller->Cookie->config('key', 'somerandomhaskeysomerandomhaskey');

        $controller->viewBuilder()->setLayout('with_flash');

        parent::controllerSpy($event, $controller);
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Controller = new SystemsController;
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

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'a']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The email was not sent');

        $contactFromView = $this->viewVariable('contact');
        $this->assertInstanceof('MeCms\Form\ContactUsForm', $contactFromView);

        //POST request. Now data are valid
        $this->post($url, [
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'email' => 'text@example.com',
            'message' => 'This is the message',
        ]);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertSession('The email has been sent', 'Flash.flash.0.message');

        //With reCAPTCHA
        Configure::write(ME_CMS . '.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('You have not filled out the reCAPTCHA control');

        $contactFromView = $this->viewVariable('contact');
        $this->assertInstanceof('MeCms\Form\ContactUsForm', $contactFromView);

        //Disabled
        Configure::write(ME_CMS . '.default.contact_us', false);
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

        //Now the current IP is banned
        Configure::write('Banned', ['99.99.99.99']);
        $this->configRequest(['environment' => ['REMOTE_ADDR' => '99.99.99.99']]);

        $this->get(['_name' => 'ipNotAllowed']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Systems/ip_not_allowed.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');
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
        Configure::write(ME_CMS . '.default.offline', true);
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
        //@codingStandardsIgnoreLine
        @unlink(SITEMAP);

        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();

        $this->assertContentType('application/x-gzip');
        $this->assertFileResponse(SITEMAP);

        //@codingStandardsIgnoreLine
        @unlink(SITEMAP);
    }
}
