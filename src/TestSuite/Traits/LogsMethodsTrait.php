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
 * @since       2.17.5
 */
namespace MeCms\TestSuite\Traits;

/**
 * This trait provides some useful methods to test logs
 */
trait LogsMethodsTrait
{
    /**
     * Asserts log file contents
     * @param string $expected The expected contents
     * @param string $name Log name
     * @param string $message The failure message that will be appended to the
     *  generated message
     * @return void
     */
    public function assertLogContains($expected, $name, $message = '')
    {
        $file = LOGS . $name . '.log';

        if (!is_readable($file)) {
            $this->fail('Log file `' . $name . '.log` not readable');
        }

        $content = trim(file_get_contents($file));

        $this->assertContains($expected, $content, $message);
    }

    /**
     * Deletes all logs file
     * @return void
     */
    public function deleteAllLogs()
    {
        foreach (glob(LOGS . '*') as $file) {
            //@codingStandardsIgnoreLine
            @unlink($file);
        }
    }

    /**
     * Deletes a log file
     * @param string $name Log name
     * @return void
     */
    public function deleteLog($name)
    {
        $file = LOGS . $name . '.log';

        if (!is_writable($file)) {
            $this->fail('Log file `' . $name . '.log` not writable');
        }

        //@codingStandardsIgnoreLine
        @unlink($file);
    }
}
