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

namespace MeCms\Controller;

use Cake\Http\Response;
use Cake\I18n\FrozenTime;
use MeCms\Form\ContactUsForm;
use MeCms\Utility\Sitemap\SitemapBuilder;
use Tools\Filesystem;

/**
 * Systems controller
 * @property \Recaptcha\Controller\Component\RecaptchaComponent $Recaptcha
 */
class SystemsController extends AppController
{
    /**
     * @var \MeCms\Form\ContactUsForm
     */
    public ContactUsForm $ContactUsForm;

    /**
     * Initialization hook method
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->ContactUsForm ??= new ContactUsForm();
    }

    /**
     * "Contact us" form
     * @return \Cake\Http\Response|null|void
     * @see \MeCms\Form\ContactUsForm
     * @see \MeCms\Mailer\ContactUsMailer
     */
    public function contactUs()
    {
        //Checks if the "contact us" form is enabled
        if (!getConfig('default.contact_us')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        if ($this->getRequest()->is('post')) {
            //Checks for reCAPTCHA, if requested
            $message = __d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA');
            if (!getConfig('security.recaptcha') || (isset($this->Recaptcha) && $this->Recaptcha->verify())) {
                //Sends the email
                $message = I18N_OPERATION_NOT_OK;
                if ($this->ContactUsForm->execute($this->getRequest()->getData())) {
                    $this->Flash->success(I18N_OPERATION_OK);

                    return $this->redirect(['_name' => 'homepage']);
                }
            }
            $this->Flash->error($message);
        }

        $this->set('contact', $this->ContactUsForm);
    }

    /**
     * "IP not allowed" page
     * @return \Cake\Http\Response|null|void
     */
    public function ipNotAllowed()
    {
        //If the user's IP address is not reported as spammer
        if (!$this->getRequest()->is('spammer')) {
            return $this->redirect($this->referer(['_name' => 'homepage']));
        }

        $this->viewBuilder()->setLayout('single-column');
    }

    /**
     * Offline page
     * @return \Cake\Http\Response|null|void
     */
    public function offline()
    {
        //If the site has not been taken offline
        if (!getConfig('default.offline')) {
            return $this->redirect($this->referer(['_name' => 'homepage']));
        }

        $this->viewBuilder()->setLayout('single-column');
    }

    /**
     * Returns the site sitemap.
     * If the sitemap doesn't exist or has expired, it generates and writes
     *  the sitemap.
     * @return \Cake\Http\Response
     * @throws \ErrorException
     */
    public function sitemap(): Response
    {
        //Checks if the sitemap exist and is not expired
        if (is_readable(SITEMAP)) {
            $time = FrozenTime::createFromTimestamp((int)filemtime(SITEMAP));

            if (!$time->modify(getConfigOrFail('main.sitemap_expiration'))->isPast()) {
                $sitemap = file_get_contents(SITEMAP);
            }
        }

        if (empty($sitemap)) {
            Filesystem::createFile(SITEMAP, gzencode(SitemapBuilder::generate(), 9));
        }

        return $this->getResponse()->withFile(SITEMAP);
    }
}
