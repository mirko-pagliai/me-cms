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
 */
namespace MeCms\Log\Engine;

use Cake\Log\Engine\FileLog;
use SerializedArray\SerializedArray;

/**
 * File Storage stream for Logging. Writes logs to different files based on
 *  the level of log it is.
 *
 * Rewrites {@link http://api.cakephp.org/3.3/class-Cake.Log.Engine.FileLog.html FileLog}.
 *
 * This adapter writes writes the normal log (using the `FileLog::log`
 *  method) and a serialized copy of the log.
 * The log information are splitted from the message, using regex.
 */
class SerializedLog extends FileLog
{
    /**
     * Gets the log as an object. It splits the log information from the
     *  message, using regex
     * @param string $level The severity level of the message being written.
     *  See Cake\Log\Log::$_levels for list of possible levels.
     * @param string $message The message you want to log.
     * @return object
     */
    protected function _getLogAsObject($level, $message)
    {
        $log['level'] = $level;
        $log['datetime'] = date('Y-m-d H:i:s');

        //Sets exception type and message
        if (preg_match('/^(\[([^\]]+)\]\s)?(.+)/', $message, $matches)) {
            if (!empty($matches[2])) {
                $log['exception'] = $matches[2];
            }

            $log['message'] = $matches[3];
        }

        //Sets the exception attributes
        if (preg_match('/Exception Attributes:\s((.(?!Request URL|Referer URL|Client IP|Stack Trace|Trace))+)/is', $message, $matches)) {
            $log['attributes'] = $matches[1];
        }

        //Sets the request URL
        if (preg_match('/^Request URL:\s(.+)$/mi', $message, $matches)) {
            $log['request'] = $matches[1];
        }

        //Sets the referer URL
        if (preg_match('/^Referer URL:\s(.+)$/mi', $message, $matches)) {
            $log['referer'] = $matches[1];
        }

        //Sets the client IP
        if (preg_match('/^Client IP:\s(.+)$/mi', $message, $matches)) {
            $log['ip'] = $matches[1];
        }

        //Sets the trace
        if (preg_match('/(Stack )?Trace:\n(.+)$/is', $message, $matches)) {
            $log['trace'] = trim($matches[2]);
        }

        //Adds the full log
        $log['full'] = trim(sprintf('%s %s: %s', date('Y-m-d H:i:s'), ucfirst($level), $message));

        return (object)$log;
    }

    /**
     * Implements writing to log files.
     *
     * Each time that is called, it writes the normal log (using the
     *  `FileLog::log` method) and a serialized copy of the log.
     * For example, if the log is `error.log`, the serialized log will be
     *  `error_serialized.log`.
     * @param string $level The severity level of the message being written.
     *  See Cake\Log\Log::$_levels for list of possible levels.
     * @param string $message The message you want to log.
     * @param array $context Additional information about the logged message
     * @return bool success of write
     * @uses _getLogAsObject()
     */
    public function log($level, $message, array $context = [])
    {
        //First of all, it normally writes log
        $parent = parent::log($level, $message, $context);

        /*
         * Now, it writes the serialized log
         */

        $message = $this->_format(trim($message), $context);
        $filename = $this->_getFilename($level);

        //It sets a new filename, adding the `_serialized` suffix.
        //For example, if the log is `error.log`, the serialized log will be
        //  `error_serialized.log`
        $filename = sprintf('%s_serialized.log', pathinfo($filename, PATHINFO_FILENAME));

        if (!empty($this->_size)) {
            $this->_rotateFile($filename);
        }

        $pathname = $this->_path . $filename;
        $mask = $this->_config['mask'];

        $data = $this->_getLogAsObject($level, $message);

        if (empty($mask)) {
            return $parent && (new SerializedArray($pathname))->prepend($data);
        }

        $exists = file_exists($pathname);
        $result = (new SerializedArray($pathname))->prepend($data);
        static $selfError = false;

        if (!$selfError && !$exists && !chmod($pathname, (int)$mask)) {
            $selfError = true;
            trigger_error(vsprintf(
                'Could not apply permission mask "%s" on log file "%s"',
                [$mask, $pathname]
            ), E_USER_WARNING);
            $selfError = false;
        }

        return $parent && $result;
    }
}
