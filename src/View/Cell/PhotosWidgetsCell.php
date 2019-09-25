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

use Cake\ORM\ResultSet;
use Cake\View\Cell;

/**
 * PhotosWidgets cell
 */
class PhotosWidgetsCell extends Cell
{
    /**
     * Initialization hook method
     * @return void
     */
    public function initialize()
    {
        $this->loadModel('MeCms.Photos');
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
            ->formatResults(function (ResultSet $results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_albums', $this->Photos->getCacheName())
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
            ->order(['created' => 'DESC', 'id' => 'DESC'])
            ->cache(sprintf('widget_latest_%d', $limit), $this->Photos->getCacheName())
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
            ->cache(sprintf('widget_random_%d', $limit), $this->Photos->getCacheName())
            ->sample($limit);

        $this->set(compact('photos'));
    }
}
