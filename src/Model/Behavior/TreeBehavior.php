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
namespace MeCms\Model\Behavior;

use Cake\ORM\Behavior\TreeBehavior as CakeTreeBehavior;

/**
 * Makes the table to which this is attached to behave like a nested set and provides methods 
 * for managing and retrieving information out of the derived hierarchical structure.
 * 
 * Rewrites {@link http://api.cakephp.org/3.1/class-Cake.ORM.Behavior.TreeBehavior.html TreeBehavior}.
 * 
 * To add to your table:
 * <code>
 * $this->addBehavior('MeCms.Tree');
 * </code> 
 */
class TreeBehavior extends CakeTreeBehavior {
	/**
	 * Gets a representation of the elements in the tree as a flat list where the keys are the 
	 * primary key for the table and the values are the display field for the table. Values 
	 * are prefixed to visually indicate relative depth in the tree
	 * @param \Cake\ORM\Query $query Query
	 * @param array $options Options
	 * @return Cake\ORM\Query Query
	 * @see http://api.cakephp.org/3.1/class-Cake.ORM.Behavior.TreeBehavior.html#_findTreeList
	 * @uses Cake\ORM\Behavior\TreeBehavior::findTreeList()
	 */
	public function findTreeList(\Cake\ORM\Query $query, array $options) {
		$options['spacer'] = empty($options['spacer']) ? '—' : $options['spacer'];
		
		return parent::findTreeList($query, $options);
	}
}