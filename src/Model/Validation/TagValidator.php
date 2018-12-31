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

use MeCms\Validation\AppValidator;

/**
 * Tag validator class
 */
class TagValidator extends AppValidator
{
    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses MeCms\Validation\AppValidator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //Tag
        $this->add('tag', [
            'validTagLength' => [
                'last' => true,
                'rule' => [$this, 'validTagLength'],
            ],
            'validTagChars' => [
                'rule' => [$this, 'validTagChars'],
            ],
        ]);
    }

    /**
     * Checks if the tag has a valid length
     * @param string $value Field value
     * @return bool|string `true` on success or an error message on failure
     */
    public function validTagLength($value)
    {
        $success = strlen($value) >= 3 && strlen($value) <= 40;

        return $success ?: __d('me_cms', 'Must be between {0} and {1} chars', 3, 40);
    }

    /**
     * Checks if the tag has a valid syntax (lowercase letters, numbers, space)
     * @param string $value Field value
     * @return bool|string `true` on success or an error message on failure
     */
    public function validTagChars($value)
    {
        $success = (bool)preg_match('/^[a-z\d\s]+$/', $value);

        return $success ?: sprintf('%s: %s', I18N_ALLOWED_CHARS, I18N_LOWERCASE_NUMBERS_SPACE);
    }
}
