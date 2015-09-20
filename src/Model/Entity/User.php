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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Entity;

use Cake\ORM\Entity;

/**
 * User entity
 */
class User extends Entity {
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        'group_id' => TRUE,
        'username' => TRUE,
        'email' => TRUE,
        'password' => TRUE,
        'first_name' => TRUE,
        'last_name' => TRUE,
        'active' => TRUE,
        'banned' => TRUE,
        'post_count' => TRUE,
        'group' => TRUE,
        'posts' => TRUE,
    ];
	
	/**
	 * Virtual fields that should be exposed
	 * @var array
	 */
    protected $_virtual = ['full_name'];
	
	/**
	 * Gets the full name (virtual field)
	 * @return string|NULL Full name
	 */
	protected function _getFullName() {
		if(empty($this->_properties['first_name']) || empty($this->_properties['last_name']))
			return;
		
		return sprintf('%s %s', $this->_properties['first_name'], $this->_properties['last_name']);
    }
	
	/**
	 * Sets the password
	 * @param string $password Password
	 * @return string Hash
	 * @uses Cake\Auth\DefaultPasswordHasher::hash()
	 */
	protected function _setPassword($password) {
        return (new \Cake\Auth\DefaultPasswordHasher)->hash($password);
    }
}