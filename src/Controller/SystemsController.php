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
namespace MeCms\Controller;

use Cake\Filesystem\File;
use Cake\I18n\Time;
use MeCms\Controller\AppController;
use MeCms\Form\ContactUsForm;
use MeCms\Utility\Sitemap;

/**
 * Systems controller
 */
class SystemsController extends AppController
{
    /**
     * Accept cookies policy.
     * It sets the cookie to remember the user accepted the cookie policy and
     *  redirects
     * @return \Cake\Network\Response|null
     */
    public function acceptCookies()
    {
        //Sets the cookie
        $this->Cookie->configKey('cookies-policy', [
            'encryption' => false,
            'expires' => '+999 days',
        ]);
        $this->Cookie->write('cookies-policy', true);

        return $this->redirect($this->referer(['_name' => 'homepage'], true));
    }

    /**
     * "Contact us" form
     * @return \Cake\Network\Response|null|void
     * @see MeCms\Form\ContactUsForm
     * @see MeCms\Mailer\ContactUsMailer
     */
    public function contactUs()
    {
        //Checks if the "contact us" form is enabled
        if (!getConfig('default.contact_us')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        $contact = new ContactUsForm;

        if ($this->request->is('post')) {
            //Checks for reCAPTCHA, if requested
            if (!getConfig('security.recaptcha') || $this->Recaptcha->verify()) {
                //Sends the email
                if ($contact->execute($this->request->getData())) {
                    $this->Flash->success(I18N_OPERATION_OK);

                    return $this->redirect(['_name' => 'homepage']);
                } else {
                    $this->Flash->error(I18N_OPERATION_NOT_OK);
                }
            } else {
                $this->Flash->error(__d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA'));
            }
        }

        $this->set(compact('contact'));
    }

    /**
     * "IP not allowed" page
     * @return \Cake\Network\Response|null|void
     */
    public function ipNotAllowed()
    {
        //If the user's IP address is not banned
        if (!$this->request->isBanned()) {
            return $this->redirect($this->referer(['_name' => 'homepage'], true));
        }

        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Offline page
     * @return \Cake\Network\Response|null|void
     */
    public function offline()
    {
        //If the site has not been taken offline
        if (!getConfig('default.offline')) {
            return $this->redirect($this->referer(['_name' => 'homepage'], true));
        }

        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Returns the site sitemap.
     * If the sitemap doesn't exist or has expired, it generates and writes
     *  the sitemap.
     * @return \Cake\Network\Response
     */
    public function sitemap()
    {
        //Checks if the sitemap exist and is not expired
        if (is_readable(SITEMAP)) {
            $time = Time::createFromTimestamp(filemtime(SITEMAP));

            if (!$time->modify(getConfigOrFail('main.sitemap_expiration'))->isPast()) {
                $sitemap = file_get_contents(SITEMAP);
            }
        }

        if (empty($sitemap)) {
            $sitemap = gzencode(Sitemap::generate(), 9);

            (new File(SITEMAP, true, 0777))->write($sitemap);
        }

        return $this->response->withFile(SITEMAP);
    }
}
