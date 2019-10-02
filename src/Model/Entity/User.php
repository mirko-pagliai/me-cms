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
namespace MeCms\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;
use Symfony\Component\Finder\Finder;

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
        if (!$this->get('first_name') || !$this->get('last_name')) {
            return null;
        }

        return sprintf('%s %s', $this->get('first_name'), $this->get('last_name'));
    }

    /**
     * Gets the picture (virtual field)
     * @return string
     */
    protected function _getPicture()
    {
        if ($this->has('id')) {
            $finder = new Finder();
            $finder->files()->name('/^' . $this->get('id') . '\..+/')->in(USER_PICTURES);
            $files = objects_map(iterator_to_array($finder), 'getFilename');

            if (!empty($files)) {
                return 'users' . DS . array_value_first($files);
            }
        }

        $path = 'no-avatar.jpg';

        return is_readable(WWW_ROOT . 'img' . DS . $path) ? $path : 'MeCms.' . $path;
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
