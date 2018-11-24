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
use MeCms\Form\ContactUsForm;
use MeCms\TestSuite\ControllerTestCase;

/**
 * SystemsControllerTest class
 */
class SystemsControllerTest extends ControllerTestCase
{
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
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
        'plugin.me_cms.Photos',
        'plugin.me_cms.PhotosAlbums',
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
    ];

    /**
     * Tests for `acceptCookies()` method
     * @test
     */
    public function testAcceptCookies()
    {
        $this->get(['_name' => 'acceptCookies']);
        $this->assertRedirect(['_name' => 'homepage']);

        $expire = Time::createFromTimestamp($this->_response->getCookie('cookies-policy')['expire']);
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
        $this->assertTemplate('Systems/contact_us.ctp');
        $this->assertInstanceof(ContactUsForm::class, $this->viewVariable('contact'));

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'a']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(ContactUsForm::class, $this->viewVariable('contact'));

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
        Configure::write('MeCms.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');
        $this->assertInstanceof(ContactUsForm::class, $this->viewVariable('contact'));

        //Disabled
        Configure::write('MeCms.default.contact_us', false);
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
        $this->assertTemplate('Systems/ip_not_allowed.ctp');
        $this->assertLayout('login.ctp');
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Systems/offline.ctp');
        $this->assertLayout('login.ctp');
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

        //GET request. The sitemap will be the same as the previous request
        $filemtime = filemtime(SITEMAP);
        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertContentType('application/x-gzip');
        $this->assertFileResponse(SITEMAP);
        $this->assertEquals($filemtime, filemtime(SITEMAP));
    }
}
