<?php
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
namespace MeCms\View\Cell;

use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
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
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        $this->loadModel(ME_CMS . '.Photos');
    }

    /**
     * Albums widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function albums($render = 'form')
    {
        $this->viewBuilder()->setTemplate(sprintf('albums_as_%s', $render));

        //Returns on albums index
        if ($this->request->isUrl(['_name' => 'albums'])) {
            return;
        }

        $albums = $this->Photos->Albums->find('active')
            ->order([sprintf('%s.title', $this->Photos->Albums->getAlias()) => 'ASC'])
            ->formatResults(function ($results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_albums', $this->Photos->cache)
            ->all();

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
                sprintf('%s.created', $this->Photos->getAlias()) => 'DESC',
                sprintf('%s.id', $this->Photos->getAlias()) => 'DESC',
            ])
            ->cache(sprintf('widget_latest_%d', $limit), $this->Photos->cache)
            ->all();

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
