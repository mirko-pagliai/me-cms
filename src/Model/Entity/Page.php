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
use Cake\Routing\Router;
use MeTools\Utility\Youtube;

/**
 * Page entity
 * @property int $id
 * @property string $title
 * @property string $subtitle
 * @property string $slug
 * @property string $text
 * @property int $priority
 * @property bool $active
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class Page extends Entity {
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        '*' => TRUE,
        'id' => FALSE,
		'modified' => FALSE,
    ];
	
	/**
	 * Virtual fields that should be exposed
	 * @var array
	 */
    protected $_virtual = ['preview'];
    
	/**
	 * Gets the post preview (virtual field)
	 * @return string Url to preview
	 * @uses MeTools\Utility\Youtube::getId()
	 * @uses MeTools\Utility\Youtube::getPreview()
	 */
	protected function _getPreview() {
		//Checks for the first image in the text
		preg_match('#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im', $this->_properties['text'], $matches);
		
		if(!empty($matches[2])) {
			return Router::url($matches[2], TRUE);
        }
        
		//Checks for a YouTube video and its preview
		preg_match('/\[youtube](.+?)\[\/youtube]/', $this->_properties['text'], $matches);
		
		if(!empty($matches[1])) {
			return Youtube::getPreview(is_url($matches[1]) ? Youtube::getId($matches[1]) : $matches[1]);
        }
        
		return;
    }
}