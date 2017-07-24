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
namespace MeCms\Model\Validation;

use Cake\Validation\Validator;

/**
 * Application validator class.
 * Used for validation of model data, it adds some default validation rules.
 *
 * Example:
 * <code>
 * public function validationDefault(Validator $validator) {
 *  $validator = new \MeCms\Model\Validation\AppValidator;
 *
 *  return $validator;
 * }
 * </code>
 */
class AppValidator extends Validator
{
    /**
     * Construct.
     *
     * Adds some default validation rules.
     * @uses Cake\Validation\Validator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //User (author)
        $this->add('user_id', [
            'naturalNumber' => [
                'message' => __d('me_cms', 'You have to select a valid option'),
                'rule' => 'naturalNumber',
            ],
        ]);

        //Email
        $this->add('email', [
            'email' => [
                'message' => __d('me_cms', 'You have to enter a valid value'),
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
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'letters, apostrophe, space'),
                    __d('me_cms', 'Has to begin with a capital letter')
                ),
                'rule' => [$this, 'personName'],
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
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'letters, apostrophe, space'),
                    __d('me_cms', 'Has to begin with a capital letter')
                ),
                'rule' => [$this, 'personName'],
            ],
        ]);

        //Title
        $this->add('title', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 100),
                'rule' => ['lengthBetween', 3, 100],
            ],
        ]);

        //Filename
        $this->add('filename', [
            'maxLength' => [
                'message' => __d('me_cms', 'Must be at most {0} chars', 255),
                'rule' => ['maxLength', 255],
            ],
        ]);

        //Subtitle
        $this->add('subtitle', [
            'lengthBetween' => [
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 150),
                'rule' => ['lengthBetween', 3, 150],
            ],
        ])->allowEmpty('subtitle');

        //Slug
        $this->add('slug', [
            'lengthBetween' => [
                'last' => true,
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 100),
                'rule' => ['lengthBetween', 3, 100],
            ],
            'slug' => [
                'message' => sprintf(
                    '%s: %s',
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'lowercase letters, numbers, dash')
                ),
                'rule' => [$this, 'slug'],
            ],
        ]);

        //Text
        $this->notEmpty('text', __d('me_cms', 'This field can not be empty'));

        //Priority
        $this->add('priority', [
            'range' => [
                'message' => __d('me_cms', 'You have to select a valid option'),
                'rule' => ['range', 1, 5],
            ],
        ]);

        //Description
        $this->add('description', [
            'maxLength' => [
                'message' => __d('me_cms', 'Must be at most {0} chars', 255),
                'rule' => ['maxLength', 255],
            ],
        ])->allowEmpty('description');

        //Active
        $this->add('active', [
            'boolean' => [
                'message' => __d('me_cms', 'You have to select a valid option'),
                'rule' => 'boolean',
            ],
        ]);

        //Created
        $this->add('created', [
            'datetime' => [
                'message' => __d('me_cms', 'You have to enter a valid value'),
                'rule' => 'datetime',
            ],
        ])->allowEmpty('created');
    }

    /**
     * Lowercase letters validation method.
     * Checks if a field contains only lowercase letters.
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     */
    public function lowercaseLetters($value, $context)
    {
        return (bool)preg_match('/^[a-z]+$/', $value);
    }

    /**
     * Person name validation method.
     * Checks if the name is a valid person name, so contains letters,
     *  apostrophe and/or space.
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     */
    public function personName($value, $context)
    {
        return (bool)preg_match('/^[A-Z][A-z\'\ ]+$/', $value);
    }

    /**
     * Slug validation method.
     * Checks if the slug is a valid slug.
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     */
    public function slug($value, $context)
    {
        //Lowercase letters, numbers, dash. At least three chars.
        //It must contain at least one letter and must begin and end with a letter or a number.
        return (bool)preg_match('/[a-z]/', $value) &&
            (bool)preg_match('/^[a-z0-9][a-z0-9\-]+[a-z0-9]$/', $value);
    }
}
