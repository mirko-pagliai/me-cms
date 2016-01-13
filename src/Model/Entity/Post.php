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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Entity;

use Cake\ORM\Entity;

/**
 * Post entity
 * @property int $id
 * @property int $category_id
 * @property \MeCms\Model\Entity\Category $category
 * @property int $user_id
 * @property \MeCms\Model\Entity\User $user
 * @property string $title
 * @property string $slug
 * @property string $subtitle
 * @property string $text
 * @property int $priority
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property bool $active
 * @property \MeCms\Model\Entity\Tag[] $tags
 */
class Post extends Entity {
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => TRUE,
        'id' => FALSE,
		'modified' => FALSE
    ];
	
	/**
	 * Virtual fields that should be exposed
	 * @var array
	 */
    protected $_virtual = ['preview', 'tags_as_string'];
	
	/**
	 * Gets the post preview (virtual field)
	 * @return string Url to preview
	 */
	protected function _getPreview() {
		//Gets the first image
		preg_match('#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im', $this->_properties['text'], $matches);
		
		if(empty($matches[2]))
			return;
		
		return \Cake\Routing\Router::url($matches[2], TRUE);
    }
	
	/**
	 * Gets tags as string, separated by a comma and a space (virtual field)
	 * @return string Tags
	 * @uses MeCms\Model\Table\TagsTable::tagsAsString()
	 */
	protected function _getTagsAsString() {
		return \Cake\ORM\TableRegistry::get('MeCms.Tags')->tagsAsString($this->_properties['tags']);
	}
}