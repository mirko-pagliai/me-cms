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
namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * Auth Helper.
 * 
 * This helper allows you to access to `MeCms\Controller\Component\AuthComponent` also from views.
 */
class AuthHelper extends Helper {
	/**
	 * Method that is called automatically when the method doesn't exist.
	 * @param string $method Method to invoke
	 * @param array $params Array of params for the method
	 * @return mixed
	 */
	public function __call($method, $params) {
        return call_user_func_array(['\MeCms\Controller\Component\AuthComponent', $method], $params);
	}
}