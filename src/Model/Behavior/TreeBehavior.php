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
 * @see         http://api.cakephp.org/4.4/class-Cake.ORM.Behavior.TreeBehavior.html
 */

namespace MeCms\Model\Behavior;

use Cake\ORM\Behavior\TreeBehavior as CakeTreeBehavior;
use Cake\ORM\Query;

/**
 * Makes the table to which this is attached to behave like a nested set and
 *  provides methods  for managing and retrieving information out of the
 *  derived hierarchical structure.
 *
 * This behavior rewrites the `TreeBehavior` class provided by CakePHP.
 */
class TreeBehavior extends CakeTreeBehavior
{
    /**
     * Gets a representation of the elements in the tree as a flat list where
     *  the keys are the primary key for the table and the values are the
     *  display field for the table. Values are prefixed to visually indicate
     *  relative depth in the tree
     * @param \Cake\ORM\Query $query Query
     * @param array $options Options
     * @return \Cake\ORM\Query Query
     */
    public function findTreeList(Query $query, array $options): Query
    {
        return parent::findTreeList($query, $options + ['spacer' => '—']);
    }
}
