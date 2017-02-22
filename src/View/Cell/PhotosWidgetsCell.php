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
namespace MeCms\View\Cell;

use Cake\View\Cell;

/**
 * PhotosWidgets cell
 */
class PhotosWidgetsCell extends Cell
{
    /**
     * Constructor
     * @param \Cake\Network\Request $request The request to use in the cell
     * @param \Cake\Network\Response $response The request to use in the cell
     * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
     * @param array $cellOptions Cell options to apply
     * @uses Cake\View\Cell::__construct()
     */
    public function __construct(
        \Cake\Network\Request $request = null,
        \Cake\Network\Response $response = null,
        \Cake\Event\EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        $this->loadModel('MeCms.Photos');
    }

    /**
     * Albums widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function albums($render = 'form')
    {
        $this->viewBuilder()->template(sprintf('albums_as_%s', $render));

        //Returns on albums index
        if ($this->request->isUrl(['_name' => 'albums'])) {
            return;
        }

        $albums = $this->Photos->Albums->find('active')
            ->order([sprintf('%s.title', $this->Photos->Albums->alias()) => 'ASC'])
            ->order(['title' => 'ASC'])
            ->formatResults(function ($results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_albums', $this->Photos->cache)
            ->toArray();

        $this->set(compact('albums'));
    }

    /**
     * Latest widget
     * @param int $limit Limit
     * @return void
     */
    public function latest($limit = 1)
    {
        //Returns on the same controllers
        if ($this->request->isController(['Photos', 'PhotosAlbums'])) {
            return;
        }

        $photos = $this->Photos->find('active')
            ->select(['album_id', 'filename'])
            ->limit($limit)
            ->order([
                sprintf('%s.created', $this->Photos->alias()) => 'DESC',
                sprintf('%s.id', $this->Photos->alias()) => 'DESC',
            ])
            ->cache(sprintf('widget_latest_%d', $limit), $this->Photos->cache)
            ->toArray();

        $this->set(compact('photos'));
    }

    /**
     * Random widget
     * @param int $limit Limit
     * @return void
     */
    public function random($limit = 1)
    {
        //Returns on the same controllers
        if ($this->request->isController(['Photos', 'PhotosAlbums'])) {
            return;
        }

        $photos = $this->Photos->find('active')
            ->select(['album_id', 'filename'])
            ->cache(sprintf('widget_random_%d', $limit), $this->Photos->cache)
            ->sample($limit)
            ->toArray();

        $this->set(compact('photos'));
    }
}