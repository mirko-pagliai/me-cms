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
 * @see         MeCms\Mailer\ContactUsMailer
 */
namespace MeCms\Form;

use Cake\Form\Form;
use Cake\Mailer\MailerAwareTrait;
use Cake\Validation\Validator;
use MeCms\Model\Validation\AppValidator;

/**
 * ContactUsForm class
 */
class ContactUsForm extends Form
{
    use MailerAwareTrait;

    /**
     * Defines the validator using the methods on Cake\Validation\Validator or
     *  loads a pre-defined validator from a concrete class.
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\AppValidator
     */
    protected function _buildValidator(Validator $validator)
    {
        $validator = new AppValidator;

        //First name
        $validator->requirePresence('first_name');

        //Last name
        $validator->requirePresence('last_name');

        //Email
        $validator->requirePresence('email');

        //Message
        $validator->add('message', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 10, 1000),
                'rule' => ['lengthBetween', 10, 1000],
            ],
        ])->requirePresence('message');

        return $validator;
    }

    /**
     * Used by `execute()` to execute the form's action. This sends the email.
     *
     * The `$data` array must contain the `email`, `first_name`, `last_name`
     *  and `message` keys.
     * @param array $data Form data
     * @return bool
     */
    protected function _execute(array $data)
    {
        return $this->getMailer(ME_CMS . '.ContactUs')->send('contactUsMail', [$data]);
    }
}
