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
 * @see         MeCms\Utility\SitemapBuilder
 */
namespace MeCms\Utility;

use Cake\I18n\FrozenTime;
use Cake\I18n\Time;

/**
 * This utility allows you to save and retrieve user login, through a special
 *  register for each user
 */
class LoginLogger
{
    /**
     * File path
     * @var string
     */
    protected $file;

    /**
     * Construct
     * @param int $id User ID
     * @return void
     * @uses $file
     */
    public function __construct($id)
    {
        $this->file = LOGIN_LOGS . 'user_' . $id . '.log';
    }

    /**
     * Internal method to parses and gets the user agent
     * @return array
     * @see https://github.com/donatj/PhpUserAgent
     */
    protected function _getUserAgent()
    {
        return parse_user_agent();
    }

    /**
     * Gets data
     * @return array
     * @uses $file
     */
    public function get()
    {
        if (!is_readable($this->file)) {
            return [];
        }

        $data = file_get_contents($this->file);

        if (empty($data)) {
            return [];
        }

        //Unserializes
        return unserialize($data);
    }

    /**
     * Saves data
     * @return bool
     * @uses $file
     * @uses _getUserAgent()
     * @uses get()
     */
    public function save()
    {
        //Gets existing data
        $data = $this->get();

        $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        $ip = getClientIp();
        $time = new FrozenTime;
        list($platform, $browser, $version) = array_values($this->_getUserAgent());

        //Removes the first record (last in order of time), if it has been saved
        //  less than an hour ago and if the user agent data are the same
        if (!empty($data[0]) && (new Time($data[0]->time))->modify('+1 hour')->isFuture()
            && $data[0]->agent === $agent && $data[0]->ip === $ip
            && $data[0]->platform === $platform && $data[0]->browser === $browser
            && $data[0]->version === $version
        ) {
            unset($data[0]);
        }

        $current = (object)compact('agent', 'ip', 'time', 'platform', 'browser', 'version');

        //Adds the current request
        array_unshift($data, $current);

        //Keeps only a specified number of records
        $data = array_slice($data, 0, config('users.login_log'));

        //Serializes and writes
        return (bool)file_put_contents($this->file, serialize($data));
    }
}
