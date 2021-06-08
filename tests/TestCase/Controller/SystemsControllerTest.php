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

namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Chronos\Chronos;
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
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
    ];

    /**
     * Tests for `acceptCookies()` method
     * @test
     */
    public function testAcceptCookies(): void
    {
        $this->get(['_name' => 'acceptCookies']);
        $this->assertRedirect(['_name' => 'homepage']);
        $expires = Time::createFromTimestamp($this->_response->getCookie('cookies-policy')['expires']);
        $this->assertCookie('1', 'cookies-policy');
        $this->assertTrue($expires->isWithinNext((string)Chronos::createFromDate(2038, 1, 1)));
    }

    /**
     * Tests for `contactUs()` method
     * @test
     */
    public function testContactUs(): void
    {
        $url = ['_name' => 'contactUs'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Systems' . DS . 'contact_us.php');
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
    public function testIpNotAllowed(): void
    {
        $this->get(['_name' => 'ipNotAllowed']);
        $this->assertRedirect(['_name' => 'homepage']);

        //With a spammer IP
        Cache::write(md5(serialize(['ip' => ['31.133.120.18']])), [
          'success' => 1,
          'ip' => [[
              'value' => '31.133.120.18',
              'appears' => 1,
            ]],
        ], 'StopSpam');
        $this->configRequest(['environment' => ['REMOTE_ADDR' => '31.133.120.18']]);
        $this->get(['_name' => 'ipNotAllowed']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Systems' . DS . 'ip_not_allowed.php');
        $this->assertLayout('login.php');
    }

    /**
     * Tests for `offline()` method
     * @test
     */
    public function testOffline(): void
    {
        $this->get(['_name' => 'offline']);
        $this->assertRedirect(['_name' => 'homepage']);

        //Offline
        Configure::write('MeCms.default.offline', true);
        $this->get(['_name' => 'offline']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Systems' . DS . 'offline.php');
        $this->assertLayout('login.php');
    }

    /**
     * Tests for `sitemap()` method
     * @test
     */
    public function testSitemap(): void
    {
        $this->loadFixtures();

        //GET request. The sitemap will be created
        @unlink(SITEMAP);
        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertFileResponse(SITEMAP);
        $this->assertContentType('application/x-gzip');

        //GET request. The sitemap will be the same as the previous request
        $filemtime = filemtime(SITEMAP);
        $this->get(['_name' => 'sitemap', 'ext' => '.xml']);
        $this->assertFileResponse(SITEMAP);
        $this->assertContentType('application/x-gzip');
        $this->assertEquals($filemtime, filemtime(SITEMAP));
    }
}
