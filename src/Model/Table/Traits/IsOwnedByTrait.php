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

namespace MeCms\Model\Table\Traits;

/**
 * This trait provides a method to check if a record is owned by a user
 */
trait IsOwnedByTrait
{
    /**
     * Checks if a record is owned by a user.
     *
     * Example:
     * <code>
     * $posts->isOwnedBy(2, 4);
     * </code>
     * it checks if the posts with ID 2 belongs to the user with ID 4.
     * @param int $recordId Record ID
     * @param int|null $userId User ID
     * @return bool
     */
    public function isOwnedBy(int $recordId, ?int $userId = null): bool
    {
        return (bool)$this->find()
            ->where(['id' => $recordId, 'user_id' => $userId])
            ->first();
    }
}
