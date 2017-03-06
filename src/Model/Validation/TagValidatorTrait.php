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
 * @since       2.15.2
 */
namespace MeCms\Model\Validation;

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
     * @param array $context Field context
     * @return bool
     */
    public function validTagLength($value, $context)
    {
        return strlen($value) >= 3 && strlen($value) <= 30;
    }

    /**
     * Checks if the tag has a valid syntax (lowercase letters, numbers, space)
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     */
    public function validTagChars($value, $context)
    {
        return (bool)preg_match('/^[a-z0-9\ ]+$/', $value);
    }
}
