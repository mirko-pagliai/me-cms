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
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use MeCms\Form\ContactUsForm;
use MeCms\TestSuite\ControllerTestCase;
use StopSpam\SpamDetector;

/**
 * SystemsControllerTest class
 * @property \MeCms\Controller\SystemsController $_controller
 */
class SystemsControllerTest extends ControllerTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
    ];

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\EventInterface $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
        parent::controllerSpy($event, $controller);

        $this->_controller->ContactUsForm->SpamDetector = $this->createPartialMock(SpamDetector::class, ['verify']);
        $this->_controller->ContactUsForm->SpamDetector->method('verify')->willReturn(true);

        $this->_controller->viewBuilder()->setLayout('with_flash');
    }

    /**
     * @uses \MeCms\Controller\SystemsController::acceptCookies()
     * @test
     */
    public function testAcceptCookies(): void
    {
        $this->get(['_name' => 'acceptCookies']);
        $this->assertRedirect(['_name' => 'homepage']);
        $expires = FrozenTime::createFromTimestamp($this->_response->getCookie('cookies-policy')['expires']);
        $this->assertCookie('1', 'cookies-policy');
        $this->assertTrue($expires->isWithinNext((string)Chronos::createFromDate(2038, 1, 1)));
    }

    /**
     * @uses \MeCms\Controller\SystemsController::contactUs()
     * @test
     */
    public function testContactUs(): void
    {
        $url = ['_name' => 'contactUs'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Systems' . DS . 'contact_us.php');
        $this->assertInstanceOf(ContactUsForm::class, $this->viewVariable('contact'));

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'a']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(ContactUsForm::class, $this->viewVariable('contact'));

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
        $this->assertInstanceOf(ContactUsForm::class, $this->viewVariable('contact'));

        //Disabled
        Configure::write('MeCms.default.contact_us', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }

    /**
     * @uses \MeCms\Controller\SystemsController::ipNotAllowed()
     * @test
     */
    public function testIpNotAllowed(): void
    {
        $this->get(['_name' => 'ipNotAllowed']);
        $this->assertRedirect(['_name' => 'homepage']);

        //With a spammer IP
        $ip = '31.133.120.18';
        Cache::write(md5(serialize(['ip' => [$ip]])), ['success' => 1, 'ip' => [['value' => $ip, 'appears' => 1]]], 'StopSpam');
        $this->configRequest(['environment' => ['REMOTE_ADDR' => $ip]]);
        $this->get(['_name' => 'ipNotAllowed']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Systems' . DS . 'ip_not_allowed.php');
        $this->assertLayout('login.php');
    }

    /**
     * @uses \MeCms\Controller\SystemsController::offline()
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
     * @uses \MeCms\Controller\SystemsController::sitemap()
     * @test
     */
    public function testSitemap(): void
    {
        //GET request. The sitemap will be created
        if (is_writable(SITEMAP)) {
            unlink(SITEMAP);
        }
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
