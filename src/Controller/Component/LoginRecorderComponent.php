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

use Cake\Collection\Collection;
use Cake\Controller\Component;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenTime;
use donatj\UserAgent\UserAgentParser;
use MeCms\Model\Entity\User;
use MeCms\Model\Table\UsersTable;

/**
 * This component allows you to save and retrieve user logins.
 *
 * You must first set the user ID with the `config()` method and the `user` value, then you can execute `read()` and
 *  `write()` methods.
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
     * @var \MeCms\Model\Entity\User
     */
    protected User $User;

    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected UsersTable $UsersTable;

    /**
     * Constructor hook method
     * @param array<string, mixed> $config The configuration settings provided to this component
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = FactoryLocator::get('Table');
        /** @var \MeCms\Model\Table\UsersTable $UsersTable */
        $UsersTable = $Locator->get('MeCms.Users');
        $this->UsersTable = $UsersTable;
    }

    /**
     * Internal method to get the `User` instance
     * @return \MeCms\Model\Entity\User
     */
    protected function getUser(): User
    {
        if (empty($this->User)) {
            /** @var \MeCms\Model\Entity\User $User */
            $User = $this->UsersTable->get($this->getConfigOrFail('user'));
            $this->User = $User;
        }

        return $this->User;
    }

    /**
     * Internal method to parses and gets the user agent
     * @param string|null $userAgent User agent string to parse or `null` to use `$_SERVER['HTTP_USER_AGENT']`
     * @return array<string, string|null>
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
     * Gets the client ip
     * @return string The client IP
     */
    public function getClientIp(): string
    {
        $ip = $this->getController()->getRequest()->clientIp();

        return $ip === '::1' ? '127.0.0.1' : $ip;
    }

    /**
     * Reads data
     * @return \Cake\Collection\Collection
     */
    public function read(): Collection
    {
        return $this->getUser()->get('last_logins');
    }

    /**
     * Saves data
     * @return bool
     */
    public function write(): bool
    {
        $lastLogins = $this->read();
        $current = $this->getUserAgent() + [
            'agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'),
            'ip' => $this->getClientIp(),
        ];

        //Removes the first record, if it has been saved less than an hour ago and if the user agent data are the same
        $first = $lastLogins->first();
        if ($first && (new FrozenTime($first['time']))->modify('+1 hour')->isFuture()) {
            array_pop($first);
            if ($first == $current) {
                $lastLogins = $lastLogins->skip(1);
            }
        }

        //Adds the current request
        $lastLogins = $lastLogins->prependItem($current + ['time' => time()]);

        //Takes only a specified number of records and writes
        $maxRows = getConfig('users.login_log');
        if ($maxRows) {
            $lastLogins = $lastLogins->take((int)$maxRows);
        }

        return (bool)$this->UsersTable->save($this->getUser()->set('last_logins', $lastLogins->toList()));
    }
}
