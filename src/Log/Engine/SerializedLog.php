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
namespace MeCms\Log\Engine;

use EntityFileLog\Log\Engine\EntityFileLog;

/**
 * File Storage stream for Logging. Writes logs to different files based on
 *  the level of log it is.
 *
 * This adapter writes writes the normal log (using the `FileLog::log`
 *  method) and a serialized copy of the log.
 * The log information are splitted from the message, using regex.
 */
class SerializedLog extends EntityFileLog
{
}
