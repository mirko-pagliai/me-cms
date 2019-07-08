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
namespace MeCms\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;

/**
 * User entity
 * @property int $id
 * @property int $group_id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property bool $active
 * @property bool $banned
 * @property int $post_count
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \MeCms\Model\Entity\Group $group
 * @property \MeCms\Model\Entity\Post[] $posts
 * @property \MeCms\Model\Entity\Token[] $tokens
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'post_count' => false,
        'modified' => false,
    ];

    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['full_name', 'picture'];

    /**
     * Gets the full name (virtual field)
     * @return string|null
     */
    protected function _getFullName()
    {
        if (empty($this->_properties['first_name']) || empty($this->_properties['last_name'])) {
            return null;
        }

        return sprintf('%s %s', $this->_properties['first_name'], $this->_properties['last_name']);
    }

    /**
     * Gets the picture (virtual field)
     * @return string
     */
    protected function _getPicture()
    {
        if (!empty($this->_properties['id'])) {
            $files = ((new Folder(USER_PICTURES))->find($this->_properties['id'] . '\..+'));

            if (!empty($files)) {
                return 'users' . DS . array_values($files)[0];
            }
        }

        //Checks for `webroot/img/no-avatar.jpg`
        if (is_readable(WWW_ROOT . 'img' . DS . 'no-avatar.jpg')) {
            return 'no-avatar.jpg';
        }

        return 'MeCms.no-avatar.jpg';
    }

    /**
     * Sets the password
     * @param string $password Password
     * @return string Hash
     */
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher())->hash($password);
    }
}
