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
use MeCms\Utility\BannerFile;

/**
 * Banner entity
 */
class Banner extends Entity {
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     * @var array
     */
    protected $_accessible = [
        'position_id' => TRUE,
        'filename' => TRUE,
        'target' => TRUE,
        'description' => TRUE,
        'active' => TRUE,
        'click_count' => TRUE,
        'position' => TRUE,
    ];
	
	/**
	 * Virtual fields that should be exposed
	 * @var array
	 */
    protected $_virtual = ['path'];
	
	/**
	 * Gets the banner path (virtual field)
	 * @return string Path
	 * @uses MeCms\Utility\BannerFile::path()
	 */
	protected function _getPath() {
        return BannerFile::path($this->_properties['filename']);
    }
}