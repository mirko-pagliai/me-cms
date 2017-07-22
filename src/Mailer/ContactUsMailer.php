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
 * @see         MeCms\Controller\SystemsController::contactUs()
 * @see         MeCms\Form\ContactUsForm
 */
namespace MeCms\Mailer;

use Cake\Network\Exception\InternalErrorException;
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
     * @throws InternalErrorException
     */
    public function contactUsMail($data)
    {
        //Checks that all required data is present
        foreach (['email', 'first_name', 'last_name', 'message'] as $key) {
            if (empty($data[$key])) {
                throw new InternalErrorException(__d('me_cms', 'Missing `{0}` key from data', $key));
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
