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
namespace MeCms\Mailer;

use MeCms\Mailer\Mailer;

/**
 * ContactFormMailer class
 */
class ContactFormMailer extends Mailer
{
    /**
     * Email for the contact form.
     *
     * The `$data` array must contain the `email`, `first_name`, `last_name`.
     * @param array $data Form data
     * @return void
     * @see MeCms\Controller\SystemsController::contactForm()
     * @see MeCms\Form\ContactForm
     */
    public function contactFormMail($data)
    {
        $this->from($data['email'], sprintf('%s %s', $data['first_name'], $data['last_name']))
            ->replyTo($data['email'], sprintf('%s %s', $data['first_name'], $data['last_name']))
            ->to(config('email.webmaster'))
            ->subject(__d('me_cms', 'Email from {0}', config('main.title')))
            ->template('MeCms.Systems/contact_form')
            ->set([
                'email' => $data['email'],
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'],
                'message' => $data['message'],
            ]);
    }
}
