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
 * PhotosAlbums controller
 * @property \MeCms\Model\Table\PhotosAlbumsTable $PhotosAlbums
 */
class PhotosAlbumsController extends AppController {
	/**
     * Lists albums
     */
    public function index() {		
		$this->set('albums', $albums = $this->PhotosAlbums->find('active')
			->select(['id', 'title', 'slug', 'photo_count'])
			->contain(['Photos' => function($q) {
				return $q
					->select(['album_id', 'filename'])
					->order('rand()');
			}])
			->order(['title' => 'ASC'])
			->cache('albums_index', 'photos')
			->all()
		);	
		
		//If there is only one album, redirects to that album
		if($albums->count() < 2)
			$this->redirect(['_name' => 'album', $albums->toArray()[0]->slug]);
    }
	
	/**
	 * Views album
	 * @param string $slug Album slug
	 */
	public function view($slug = NULL) {
		//The slug can be passed as query string, from a widget
		if($this->request->query('q'))
			$this->redirect([$this->request->query('q')]);
		
		$this->set('album', $this->PhotosAlbums->find('active')
			->contain(['Photos' => [
				'fields' => ['id', 'album_id', 'filename', 'description']
			]])
			->select(['id', 'title'])
			->where(compact('slug'))
			->cache(sprintf('albums_view_%s', md5($slug)), 'photos')
			->first()
		);
	}
}