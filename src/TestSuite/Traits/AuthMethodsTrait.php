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
namespace MeCms\TestSuite\Traits;

trait AuthMethodsTrait
{
    public function assertGroupsAreAuthorized($values)
    {
        foreach ($values as $group => $isAllowed) {
            $this->setUserGroup($group);
            $this->assertEquals($isAllowed, $this->Controller->isAuthorized());
        }
    }

    public function assertUsersAreAuthorized($values)
    {
        foreach ($values as $id => $isAllowed) {
            $this->setUserId($id);
            $this->assertEquals($isAllowed, $this->Controller->isAuthorized());
        }

    }

    protected function setUserId($id)
    {
        $this->Controller->Auth->setUser(['id' => $id]);
        $this->session(['Auth' => ['User' => ['id' => $id]]]);
    }

    /**
     * Internal method to set the user group
     * @param string $group Group name
     */
    protected function setUserGroup($group)
    {
        $this->Controller->Auth->setUser(['group' => ['name' => $group]]);
        $this->session(['Auth' => ['User' => ['group' => ['name' => $group]]]]);
    }
}
