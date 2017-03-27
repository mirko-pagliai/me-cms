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
namespace MeCms\Controller\Component;

use Cake\Controller\Component;
use Cake\I18n\Time;
use Cake\Network\Exception\InternalErrorException;
use SerializedArray\SerializedArray;

/**
 * This component allows you to save and retrieve user logins, through a special
 *  register for each user.
 *
 * You must first set the user ID with the `setUser()` method, then you can
 *  execute `read()` and `write()` methods.
 */
class LoginRecorderComponent extends Component
{
    /**
     * @var \MeTools\Utility\SerializedArray
     */
    protected $SerializedArray;

    /**
     * User ID
     * @var int
     */
    protected $user;

    /**
     * Internal method to parses and gets the user agent
     * @param string|null $userAgent User agent string to parse or `null` to
     *  use `$_SERVER['HTTP_USER_AGENT']`
     * @return array
     * @see https://github.com/donatj/PhpUserAgent
     */
    protected function getUserAgent($userAgent = null)
    {
        return parse_user_agent($userAgent);
    }

    /**
     * Sets the user ID
     * @param int $id User ID
     * @return $this
     * @uses $SerializedArray
     * @throws InternalErrorException
     */
    public function setUser($id)
    {
        if (!isPositive($id)) {
            throw new InternalErrorException(__d('me_cms', 'Invalid value'));
        }

        $this->SerializedArray = new SerializedArray(LOGIN_RECORDS . 'user_' . $id . '.log');

        return $this;
    }

    /**
     * Gets data
     * @return array
     * @uses $SerializedArray
     * @throws InternalErrorException
     */
    public function read()
    {
        if (empty($this->SerializedArray)) {
            throw new InternalErrorException(__d('me_cms', 'You must first set the user ID'));
        }

        return $this->SerializedArray->read();
    }

    /**
     * Saves data
     * @return bool
     * @uses $SerializedArray
     * @uses getUserAgent()
     * @uses read()
     * @throws InternalErrorException
     */
    public function write()
    {
        if (empty($this->SerializedArray)) {
            throw new InternalErrorException(__d('me_cms', 'You must first set the user ID'));
        }

        //Gets existing data
        $data = $this->read();

        $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        $ip = $this->getController()->request->clientIp();
        $time = new Time;
        list($platform, $browser, $version) = array_values($this->getUserAgent());

        //Removes the first record (last in order of time), if it has been saved
        //  less than an hour ago and if the user agent data are the same
        if (!empty($data[0]) && (new Time($data[0]->time))->modify('+1 hour')->isFuture()
            && $data[0]->agent === $agent && $data[0]->ip === $ip
            && $data[0]->platform === $platform && $data[0]->browser === $browser
            && $data[0]->version === $version
        ) {
            unset($data[0]);
        }

        //Adds the current request
        array_unshift($data, (object)compact('agent', 'ip', 'time', 'platform', 'browser', 'version'));

        //Keeps only a specified number of records
        $data = array_slice($data, 0, config('users.login_log'));

        //Writes
        return $this->SerializedArray->write($data);
    }
}
