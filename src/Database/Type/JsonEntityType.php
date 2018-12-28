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
 * @since       2.23.0
 */
namespace MeCms\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type\JsonType;
use Cake\ORM\Entity;

/**
 * Json Entity type converter.
 *
 * Use to convert an array of `Entity` as json data.
 */
class JsonEntityType extends JsonType
{
    /**
     * Convert string values to PHP arrays.
     * @param mixed $value The value to convert
     * @param \Cake\Database\Driver $driver The driver instance to convert with
     * @return mixed
     */
    public function toPHP($value, Driver $driver)
    {
        $value = parent::toPHP($value, $driver);

        return is_array($value) ? array_map(function ($value) {
            return is_array($value) ? new Entity($value) : $value;
        }, $value) : $value;
    }
}
