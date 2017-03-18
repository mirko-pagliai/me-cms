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
 * @since       2.16.1
 */
namespace MeCms\Controller\Traits;

use Cake\Network\Exception\InternalErrorException;

/**
 * This trait provides an internal method to download files
 */
trait DownloadTrait
{
    /**
     * Internal method to download a file
     * @param string $path File path
     * @param bool $force If `true`, it forces the download
     * @return \Cake\Network\Response
     * @throws InternalErrorException
     */
    protected function _download($path, $force = true)
    {
        if (!is_readable($path)) {
            throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($path)));
        }

        $this->response->file($path, ['download' => $force]);

        return $this->response;
    }
}
