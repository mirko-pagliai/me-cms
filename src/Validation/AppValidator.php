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

namespace MeCms\Validation;

use Cake\Validation\Validator;

/**
 * Application validator class.
 *
 * This class adds some common rules and provides some common methods for all validators.
 */
class AppValidator extends Validator
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        //User (author)
        $this->add('user_id', [
            'naturalNumber' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'naturalNumber',
            ],
        ]);

        //Email
        $this->add('email', [
            'email' => [
                'message' => I18N_ENTER_VALID_VALUE,
                'rule' => 'email',
            ],
            'maxLength' => [
                'message' => __d('me_cms', 'Must be at most {0} chars', 100),
                'rule' => ['maxLength', 100],
            ],
        ]);

        //First name
        $this->add('first_name', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 40),
                'rule' => ['lengthBetween', 3, 40],
            ],
            'personName' => [
                'message' => sprintf(
                    '%s: %s. %s',
                    I18N_ALLOWED_CHARS,
                    __d('me_cms', 'letters, apostrophe, space'),
                    __d('me_cms', 'Has to begin with a capital letter')
                ),
                'rule' => ['custom', '/^[A-Z][A-z\'\ ]+$/'],
            ],
        ]);

        //Last name
        $this->add('last_name', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 40),
                'rule' => ['lengthBetween', 3, 40],
            ],
            'personName' => [
                'message' => sprintf(
                    '%s: %s. %s',
                    I18N_ALLOWED_CHARS,
                    __d('me_cms', 'letters, apostrophe, space'),
                    __d('me_cms', 'Has to begin with a capital letter')
                ),
                'rule' => ['custom', '/^[A-Z][A-z\'\ ]+$/'],
            ],
        ]);

        //Title
        $this->add('title', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 100),
                'rule' => ['lengthBetween', 3, 100],
            ],
        ]);

        //Subtitle
        $this->add('subtitle', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 150),
                'rule' => ['lengthBetween', 3, 150],
            ],
        ])->allowEmptyString('subtitle');

        //Slug
        $this->add('slug', [
            'lengthBetween' => [
                'last' => true,
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 100),
                'rule' => ['lengthBetween', 3, 100],
            ],
            'slug' => [
                'message' => sprintf('%s: %s', I18N_ALLOWED_CHARS, I18N_LOWERCASE_NUMBERS_DASH),
                'rule' => [$this, 'slug'],
            ],
        ]);

        //Text
        $this->notEmptyString('text', __d('me_cms', 'This field can not be empty'));

        //Priority
        $this->add('priority', [
            'range' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => ['range', 1, 5],
            ],
        ]);

        //Description
        $this->add('description', [
            'maxLength' => [
                'message' => __d('me_cms', 'Must be at most {0} chars', 255),
                'rule' => ['maxLength', 255],
            ],
        ])->allowEmptyString('description');

        //Active
        $this->add('active', [
            'boolean' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'boolean',
            ],
        ]);

        //Created
        $this->add('created', [
            'datetime' => [
                'message' => I18N_ENTER_VALID_VALUE,
                'rule' => 'datetime',
            ],
        ])->allowEmptyDateTime('created');
    }

    /**
     * Slug validation method.
     * Checks if the slug is a valid slug.
     * @param string $value Field value
     * @return bool
     */
    public function slug(string $value): bool
    {
        //Lowercase letters, numbers, dash. At least three chars.
        //It must contain at least one letter and must begin and end with a letter or a number.
        return preg_match('/[a-z]/', $value) && preg_match('/^[a-z\d][a-z\d\-]+[a-z\d]$/', $value);
    }
}
