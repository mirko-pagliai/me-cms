<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace MeCms\Form;

use Cake\Event\EventManager;
use Cake\Form\Form;
use Cake\Mailer\MailerAwareTrait;
use Cake\Validation\Validator;
use MeCms\Validation\AppValidator;
use StopSpam\SpamDetector;

/**
 * ContactUsForm class
 * @see \MeCms\Controller\SystemsController::contactUs()
 * @see \MeCms\Mailer\ContactUsMailer
 */
class ContactUsForm extends Form
{
    use MailerAwareTrait;

    /**
     * @var \StopSpam\SpamDetector
     */
    public SpamDetector $SpamDetector;

    /**
     * Constructor
     *
     * @param \Cake\Event\EventManager|null $eventManager The event manager.
     *  Defaults to a new instance.
     */
    public function __construct(?EventManager $eventManager = null)
    {
        parent::__construct($eventManager);

        $this->SpamDetector ??= new SpamDetector();
    }

    /**
     * Returns the default validator object
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = new AppValidator();

        //First name
        $validator->requirePresence('first_name');

        //Last name
        $validator->requirePresence('last_name');

        //Email
        $validator->add('email', [
            'notSpammer' => [
                'message' => __d('me_cms', 'This email address has been reported as a spammer'),
                'rule' => fn(string $email): bool => $this->SpamDetector->email($email)->verify(),
            ],
        ])->requirePresence('email');

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
     * The `$data` array must contain the `email`, `first_name`, `last_name` and `message` keys.
     * @param array $data Form data
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        return (bool)$this->getMailer('MeCms.ContactUs')->send('contactUsMail', [$data]);
    }
}
