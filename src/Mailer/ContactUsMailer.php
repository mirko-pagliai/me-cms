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
 * @see         MeCms\Controller\SystemsController::contactUs()
 * @see         MeCms\Form\ContactUsForm
 */
namespace MeCms\Mailer;

use InvalidArgumentException;
use MeCms\Mailer\Mailer;

/**
 * ContactUsMailer class
 */
class ContactUsMailer extends Mailer
{
    /**
     * Email for the "contact us" form.
     *
     * The `$data` array must contain the `email`, `first_name`, `last_name`
     *  and `message` keys.
     * @param array $data Form data
     * @return void
     * @throws InvalidArgumentException
     */
    public function contactUsMail($data)
    {
        //Checks that all required data is present
        foreach (['email', 'first_name', 'last_name', 'message'] as $key) {
            if (empty($data[$key])) {
                throw new InvalidArgumentException(__d('me_cms', 'Missing `{0}` key from data', $key));
            }
        }

        $this->setSender($data['email'], sprintf('%s %s', $data['first_name'], $data['last_name']))
            ->setReplyTo($data['email'], sprintf('%s %s', $data['first_name'], $data['last_name']))
            ->setTo(getConfigOrFail('email.webmaster'))
            ->setSubject(__d('me_cms', 'Email from {0}', getConfigOrFail('main.title')))
            ->setTemplate(ME_CMS . '.Systems/contact_us')
            ->setViewVars([
                'email' => $data['email'],
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'],
                'message' => $data['message'],
            ]);
    }
}
