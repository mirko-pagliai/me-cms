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
namespace MeCms\Controller;

use MeCms\Controller\AppController;

/**
 * Banners controller
 * @property \MeCms\Model\Table\BannersTable $Banners
 */
class BannersController extends AppController {
	/**
	 * Open a banner target (link)
	 * @param string $id Banner ID
	 * @throws \Cake\Network\Exception\NotFoundException
	 */
	public function open($id = NULL) {
		$banner = $this->Banners->find('active')
			->select(['id', 'target'])
			->where(compact('id'))
			->limit(1)
			->cache(sprintf('view_%s', md5($id)), $this->Banners->cache)
			->first();
		
		if(empty($banner) || empty($banner->target))
			throw new \Cake\Network\Exception\NotFoundException(__d('me_cms', 'The banner target is missing'));
				
		//Increases the click count
		$expression = new \Cake\Database\Expression\QueryExpression('click_count = click_count + 1');
		$this->Banners->updateAll([$expression], [compact('id')]);
		
		//Redirects
		$this->redirect($banner->target);
	}
}