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
namespace MeCms\Model\Table\Traits;

/**
 * This trait provides a method to check if a record is owned by an user
 */
trait IsOwnedByTrait
{
    /**
     * Checks if a record is owned by an user.
     *
     * Example:
     * <code>
     * $posts->isOwnedBy(2, 4);
     * </code>
     * it checks if the posts with ID 2 belongs to the user with ID 4.
     * @param int $recordId Record ID
     * @param int $userId User ID
     * @return bool
     */
    public function isOwnedBy($recordId, $userId = null)
    {
        return (bool)$this->find()
            ->where([
                'id' => $recordId,
                'user_id' => $userId,
             ])
            ->first();
    }
}
