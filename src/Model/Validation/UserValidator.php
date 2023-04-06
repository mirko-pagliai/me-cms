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

namespace MeCms\Model\Validation;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Locator\LocatorAwareTrait;
use MeCms\Validation\AppValidator;

/**
 * User validator class
 */
class UserValidator extends AppValidator
{
    use LocatorAwareTrait;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->add('group_id', [
            'naturalNumber' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'naturalNumber',
            ],
        ])->requirePresence('group_id', 'create');

        $this->add('username', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 4, 40),
                'rule' => ['lengthBetween', 4, 40],
            ],
            'slug' => [
                'message' => sprintf('%s: %s', I18N_ALLOWED_CHARS, I18N_LOWERCASE_NUMBERS_DASH),
                'rule' => [$this, 'slug'],
            ],
            'notReservedWord' => [
                'message' => __d('me_cms', 'This value contains a reserved word'),
                'rule' => ['custom', '/^((?!admin|manager|root|supervisor|moderator).)+$/i'],
            ],
        ])->requirePresence('username', 'create');

        $this->requirePresence('email', 'create');

        $this->add('email_repeat', [
            'compareWith' => [
                'message' => __d('me_cms', 'Email addresses don\'t match'),
                'rule' => ['compareWith', 'email'],
            ],
        ]);

        $this->add('password', [
            'minLength' => [
                'last' => true,
                'message' => __d('me_cms', 'Must be at least {0} chars', 8),
                'rule' => ['minLength', 8],
            ],
            'passwordContainsDigit' => [
                'message' => __d('me_cms', 'The password should contain at least one digit'),
                'rule' => fn(string $password): bool => preg_match('/\d/', $password) === 1,
            ],
            'passwordContainsLowercaseLetter' => [
                'message' => __d('me_cms', 'The password should contain at least one lowercase letter'),
                'rule' => fn(string $password): bool => preg_match('/[a-z]/', $password) === 1,
            ],
            'passwordContainsCapitalLetter' => [
                'message' => __d('me_cms', 'The password should contain at least one capital letter'),
                'rule' => fn(string $password): bool => preg_match('/[A-Z]/', $password) === 1,
            ],
            'passwordContainsSymbol' => [
                'message' => __d('me_cms', 'The password should contain at least one symbol'),
                'rule' => fn(string $password): bool => preg_match('/[^A-z\d]/', $password) === 1,
            ],
        ])->requirePresence('password', 'create')->notEmptyString('password');

        $this->add('password_repeat', [
            'compareWith' => [
                'message' => __d('me_cms', 'Passwords don\'t match'),
                'rule' => ['compareWith', 'password'],
            ],
        ])->requirePresence('password_repeat', 'create')->notEmptyString('password_repeat');

        $this->add('password_old', [
            'oldPasswordIsRight' => [
                'message' => __d('me_cms', 'The old password is wrong'),
                'rule' => function (string $value, array $context): bool {
                    //Gets the old password and checks if the password matches
                    /** @var \MeCms\Model\Table\UsersTable $Users */
                    $Users = $this->getTableLocator()->get('MeCms.Users');
                    /** @var \MeCms\Model\Entity\User $User */
                    $User = $Users->findById($context['data']['id'])->select(['password'])->firstOrFail();

                    return (new DefaultPasswordHasher())->check($value, $User->get('password'));
                },
            ],
        ]);

        $this->requirePresence('first_name', 'create');

        $this->requirePresence('last_name', 'create');

        $this->add('banned', [
            'boolean' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'boolean',
            ],
        ])->allowEmptyString('banned');
    }
}
