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
namespace MeCms\Controller\Component;

use Cake\Controller\Component;
use Cake\I18n\Time;
use InvalidArgumentException;
use SerializedArray\SerializedArray;

/**
 * This component allows you to save and retrieve user logins, through a special
 *  register for each user.
 *
 * You must first set the user ID with the `config()` method and the `user`
 *  value, then you can execute `read()` and `write()` methods.
 *
 * Example:
 * <code>
 * $this->LoginRecorder->config('user', 1);
 * $data = $this->LoginRecorder->read();
 * </code>
 */
class LoginRecorderComponent extends Component
{
    /**
     * @var \MeTools\Utility\SerializedArray
     */
    protected $SerializedArray;

    /**
     * Internal method to get the client ip
     * @return string The client IP
     */
    protected function getClientIp()
    {
        $ip = $this->getController()->request->clientIp();

        if ($ip === '::1') {
            return '127.0.0.1';
        }

        return $ip;
    }

    /**
     * Gets the `SerializedArray` instance
     * @return \MeTools\Utility\SerializedArray
     * @throws InvalidArgumentException
     */
    protected function getSerializedArray()
    {
        $user = $this->getConfig('user');

        if (!is_positive($user)) {
            throw new InvalidArgumentException(__d('me_cms', 'You have to set a valid user id'));
        }

        return $this->SerializedArray = new SerializedArray(LOGIN_RECORDS . 'user_' . $user . '.log');
    }

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
     * Gets data
     * @return array
     * @uses getSerializedArray()
     */
    public function read()
    {
        return $this->getSerializedArray()->read();
    }

    /**
     * Saves data
     * @return bool
     * @uses getClientIp()
     * @uses getSerializedArray()
     * @uses getUserAgent()
     * @uses read()
     */
    public function write()
    {
        //Gets existing data
        $data = $this->read();

        $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        $ip = $this->getClientIp();
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
        $data = array_slice($data, 0, getConfig('users.login_log'));

        //Writes
        return $this->getSerializedArray()->write($data);
    }
}
