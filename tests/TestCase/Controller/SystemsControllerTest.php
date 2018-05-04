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

use Cake\Core\Configure;
use Cake\I18n\Time;
use MeCms\Controller\SystemsController;
use MeCms\TestSuite\IntegrationTestCase;

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
        parent::controllerSpy($event, $controller);

        $this->_controller->Cookie->config('key', 'somerandomhaskeysomerandomhaskey');
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Systems/contact_us.ctp');

        $contactFromView = $this->viewVariable('contact');
        $this->assertNotEmpty($contactFromView);
        $this->assertInstanceof('MeCms\Form\ContactUsForm', $contactFromView);

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'a']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);

        $contactFromView = $this->viewVariable('contact');
        $this->assertNotEmpty($contactFromView);
        $this->assertInstanceof('MeCms\Form\ContactUsForm', $contactFromView);

        //POST request. Now data are valid
        $this->post($url, [
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'email' => 'text@example.com',
            'message' => 'This is the message',
        ]);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //With reCAPTCHA
        Configure::write(ME_CMS . '.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        $contactFromView = $this->viewVariable('contact');
        $this->assertNotEmpty($contactFromView);
        $this->assertInstanceof('MeCms\Form\ContactUsForm', $contactFromView);

        //Disabled
        Configure::write(ME_CMS . '.default.contact_us', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
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
        $this->assertResponseOkAndNotEmpty();
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Systems/offline.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');
    }

    /**
     * Tests for `sitemap()` method
     * @test
     */
    public function testSitemap()
    {
        $this->loadFixtures();

        safe_unlink(SITEMAP);

        //GET request. The sitemap will be created
        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertContentType('application/x-gzip');
        $this->assertFileResponse(SITEMAP);

        $filemtime = filemtime(SITEMAP);

        //GET request. The sitemap will be the same as the previous request
        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertContentType('application/x-gzip');
        $this->assertFileResponse(SITEMAP);

        $this->assertEquals($filemtime, filemtime(SITEMAP));
    }
}
