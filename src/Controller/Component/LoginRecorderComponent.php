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
use Cake\ORM\Entity;
use donatj\UserAgent\UserAgentParser;

/**
 * This component allows you to save and retrieve user logins.
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
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $UsersTable;

    /**
     * Constructor hook method
     * @param array<string, mixed> $config The configuration settings provided to this component
     * @return void
     */
    public function initialize($config): void
    {
        parent::initialize($config);

        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = FactoryLocator::get('Table');
        /** @var \MeCms\Model\Table\UsersTable $UsersTable */
        $UsersTable = $Locator->get('MeCms.Users');
        $this->UsersTable = $UsersTable;
    }

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
     * @return \Cake\Collection\Collection
     */
    public function read(): Collection
    {
        return $this->UsersTable->get($this->getConfig('user'))->get('last_logins');
    }

    /**
     * Saves data
     * @return bool
     */
    public function write(): bool
    {
        $User = $this->UsersTable->get($this->getConfig('user'));
        $lastLogins = $User->get('last_logins');

        //Removes the first record, if it has been saved less than an hour ago
        //  and if the user agent data are the same
        $current = $this->getUserAgent() + [
            'agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'),
            'ip' => $this->getClientIp(),
        ];
        $first = $lastLogins->first();
        if ($first && (new FrozenTime($first->get('time')))->modify('+1 hour')->isFuture() && $first->extract(['agent', 'browser', 'ip', 'platform', 'version']) == $current) {
            $lastLogins = $lastLogins->skip(1);
        }

        //Adds the current request, takes only a specified number of records and writes
        $lastLogins = $lastLogins->prependItem(new Entity($current + ['time' => new FrozenTime()]));
        $lastLogins = $lastLogins->take((int)getConfig('users.login_log'));

        $User = $User->set('last_logins', $lastLogins->toList());

        return (bool)$this->UsersTable->save($User);
    }
}
