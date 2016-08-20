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
     * Gets the log as an array. It splits the log information from the
     *  message, using regex
     * @param string $level The severity level of the message being written.
     *  See Cake\Log\Log::$_levels for list of possible levels.
     * @param string $message The message you want to log.
     * @return array
     */
    protected function _getLogAsArray($level, $message)
    {
        $serialized['level'] = $level;
        $serialized['datetime'] = date('Y-m-d H:i:s');

        //Sets exception type and message
        if (preg_match('/^(\[([^\]]+)\]\s)?(.+)/', $message, $matches)) {
            if (!empty($matches[2])) {
                $serialized['exception'] = $matches[2];
            }

            $serialized['message'] = $matches[3];
        }

        //Sets the exception attributes
        if (preg_match('/Exception Attributes:\s((.(?!Request URL|Referer URL|Client IP|Stack Trace|Trace))+)/is', $message, $matches)) {
            $serialized['attributes'] = $matches[1];
        }

        //Sets the request URL
        if (preg_match('/^Request URL:\s(.+)$/mi', $message, $matches)) {
            $serialized['request'] = $matches[1];
        }

        //Sets the referer URL
        if (preg_match('/^Referer URL:\s(.+)$/mi', $message, $matches)) {
            $serialized['referer'] = $matches[1];
        }

        //Sets the client IP
        if (preg_match('/^Client IP:\s(.+)$/mi', $message, $matches)) {
            $serialized['ip'] = $matches[1];
        }

        //Sets the trace
        if (preg_match('/(Stack )?Trace:\n(.+)$/is', $message, $matches)) {
            $serialized['trace'] = $matches[2];
        }

        //Adds the full log
        $serialized['full'] = sprintf('%s %s: %s', date('Y-m-d H:i:s'), ucfirst($level), $message);

        return $serialized;
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
     * @uses _getLogAsArray()
     */
    public function log($level, $message, array $context = [])
    {
        //First of all, it normally writes log
        parent::log($level, $message, $context);

        //Now, it writes the serialized log
        $message = $this->_format(trim($message), $context);

        $filename = $this->_getFilename($level);

        //It sets a new filename, adding the `_serialized` suffix
        //For example, if the log is `error.log`, the serialized log will be `error_serialized.log`
        $filename = sprintf('%s_serialized.log', pathinfo($filename, PATHINFO_FILENAME));

        if (!empty($this->_size)) {
            $this->_rotateFile($filename);
        }

        $pathname = $this->_path . $filename;
        $mask = $this->_config['mask'];

        //Gets the content of the existing logs and unserializes
        if (is_readable($pathname)) {
            $logs = unserialize(file_get_contents($pathname));
        }
        
        if (empty($logs) || !is_array($logs)) {
            $logs = [];
        }

        //Adds the current log at the beginning
        array_unshift($logs, (object)$this->_getLogAsArray($level, $message));

        //Serializes logs
        $output = serialize($logs);

        if (empty($mask)) {
            return file_put_contents($pathname, $output);
        }

        $exists = file_exists($pathname);
        $result = file_put_contents($pathname, $output);
        static $selfError = false;

        if (!$selfError && !$exists && !chmod($pathname, (int)$mask)) {
            $selfError = true;
            trigger_error(vsprintf(
                'Could not apply permission mask "%s" on log file "%s"',
                [$mask, $pathname]
            ), E_USER_WARNING);
            $selfError = false;
        }

        return $result;
    }
}
