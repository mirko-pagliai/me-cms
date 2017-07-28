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
 * @since       2.20.0
 */
namespace MeCms\TestSuite;

use MeTools\TestSuite\TestCase;

/**
 * ValidationTestCase class
 */
class ValidationTestCase extends TestCase
{
    /**
     * Assert that all data are required.
     *
     * It first verifies that the entity, compiled with passed data, has no
     *  errors.
     *
     * Then it removes one field at a time, verifying that the "this field is
     *  required" error is generated.
     * @param object $table Table instance
     * @param array $data Valid data
     * @param array $exclude Key to be excluded
     * @return void
     */
    public function assertAllDataAreRequired($table, $data, $exclude = [])
    {
        $this->assertEmpty($table->newEntity($this->example)->getErrors());

        foreach (array_keys($data) as $key) {
            if (in_array($key, $exclude)) {
                continue;
            }

            //Create a copy of the example data and removes the current value
            $copy = $data;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $table->newEntity($copy)->getErrors());
        }
    }
}
