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
 * @since       2.15.2
 */
namespace MeCms\Model\Validation\Traits;

/**
 * Tag validator trait class.
 *
 * It provides some methods shared by the validation classes.
 */
trait TagValidatorTrait
{
    /**
     * Checks if the tag has a valid length
     * @param string $value Field value
     * @return bool
     */
    public function validTagLength($value)
    {
        return strlen($value) >= 3 && strlen($value) <= 30;
    }

    /**
     * Checks if the tag has a valid syntax (lowercase letters, numbers, space)
     * @param string $value Field value
     * @return bool
     */
    public function validTagChars($value)
    {
        return (bool)preg_match('/^[a-z\d\s]+$/', $value);
    }
}
