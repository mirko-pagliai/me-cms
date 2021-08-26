<?php
declare(strict_types=1);

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
use Cake\ORM\Entity;
use donatj\UserAgent\UserAgentParser;
use InvalidArgumentException;
use Tools\Exceptionist;
use Tools\FileArray;

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
     * @var \Tools\FileArray
     */
    protected $FileArray;

    /**
     * Internal method to get the client ip
     * @return string The client IP
     */
    protected function getClientIp(): string
    {
        $ip = $this->getController()->getRequest()->clientIp();

        return $ip === '::1' ? '127.0.0.1' : $ip;
    }

    /**
     * Gets the `FileArray` instance
     * @return \Tools\FileArray
     * @throws \InvalidArgumentException
     * @uses $FileArray
     */
    public function getFileArray(): FileArray
    {
        if (!$this->FileArray) {
            $user = $this->getConfig('user');
            Exceptionist::isPositive($user, __d('me_cms', 'You have to set a valid user id'), InvalidArgumentException::class);
            $this->FileArray = new FileArray(LOGIN_RECORDS . 'user_' . $user . '.log');
        }

        return $this->FileArray;
    }

    /**
     * Internal method to parses and gets the user agent
     * @param string|null $userAgent User agent string to parse or `null` to
     *  use `$_SERVER['HTTP_USER_AGENT']`
     * @return array
     * @see https://github.com/donatj/PhpUserAgent
     */
    protected function getUserAgent(?string $userAgent = null): array
    {
        $parser = (new UserAgentParser())->parse($userAgent);

        return [
            'platform' => $parser->platform(),
            'browser' => $parser->browser(),
            'version' => $parser->browserVersion(),
        ];
    }

    /**
     * Reads data
     * @return array
     * @uses getFileArray()
     */
    public function read(): array
    {
        return $this->getFileArray()->read();
    }

    /**
     * Saves data
     * @return bool
     * @uses getClientIp()
     * @uses getFileArray()
     * @uses getUserAgent()
     */
    public function write(): bool
    {
        $current = $this->getUserAgent() + [
            'agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'),
            'ip' => $this->getClientIp(),
        ];
        $last = $this->getFileArray()->exists(0) ? $this->getFileArray()->get(0) : [];

        //Removes the first record (last in order of time), if it has been saved
        //  less than an hour ago and if the user agent data are the same
        if ($last
            && (new Time($last->get('time')))->modify('+1 hour')->isFuture()
            && $last->extract(['agent', 'browser', 'ip', 'platform', 'version']) == $current
        ) {
            $this->getFileArray()->delete(0);
        }

        //Adds the current request, takes only a specified number of records and writes
        return $this->getFileArray()->prepend(new Entity($current + ['time' => new Time()]))
            ->take((int)getConfig('users.login_log'))
            ->write();
    }
}
