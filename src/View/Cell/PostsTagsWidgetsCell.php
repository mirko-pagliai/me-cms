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
use InvalidArgumentException;

/**
 * PostsTagsWidgets cell
 */
class PostsTagsWidgetsCell extends Cell
{
    /**
     * Constructor. It loads the model
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

        $this->loadModel(ME_CMS . '.Tags');
    }

    /**
     * Internal method to get the font sizes
     * @param array|bool $style Style for tags. Array with `maxFont` and
     *  `minFont` keys or `false` to disable
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getFontSizes(array $style)
    {
        //Maximum and minimun font sizes we want to use
        $maxFont = empty($style['maxFont']) ? 40 : $style['maxFont'];
        $minFont = empty($style['minFont']) ? 12 : $style['minFont'];

        if ($maxFont <= $minFont) {
            throw new InvalidArgumentException(__d('me_cms', 'Invalid values'));
        }

        return [$maxFont, $minFont];
    }

    /**
     * Popular tags widgets
     * @param int $limit Limit
     * @param string $prefix Prefix for each tag. This works only with the cloud
     * @param string $render Render type (`cloud`, `form` or `list`)
     * @param bool $shuffle Shuffles tags
     * @param array|bool $style Style for tags. Array with `maxFont` and
     *  `minFont` keys or `false` to disable
     * @return void
     * @uses getFontSizes()
     */
    public function popular(
        $limit = 10,
        $prefix = '#',
        $render = 'cloud',
        $shuffle = true,
        $style = ['maxFont' => 40, 'minFont' => 12]
    ) {
        $this->viewBuilder()->setTemplate(sprintf('popular_as_%s', $render));

        //Returns on tags index
        if ($this->request->isUrl(['_name' => 'postsTags'])) {
            return;
        }

        //Sets default maximum and minimun font sizes we want to use
        $maxFont = $minFont = 0;

        //Sets the initial cache name
        $cache = sprintf('widget_tags_popular_%s', $limit);

        if ($style && is_array($style)) {
            //Updates maximum and minimun font sizes we want to use
            list($maxFont, $minFont) = $this->getFontSizes($style);

            //Updates the cache name
            $cache = sprintf('%s_max_%s_min_%s', $cache, $maxFont, $minFont);
        }

        $tags = $this->Tags->find()
            ->select(['tag', 'post_count'])
            ->limit($limit)
            ->order([
                sprintf('%s.post_count', $this->Tags->getAlias()) => 'DESC',
                sprintf('%s.tag', $this->Tags->getAlias()) => 'ASC',
            ])
            ->formatResults(function ($results) use ($style, $maxFont, $minFont) {
                if (!$results->count()) {
                    return $results;
                }

                $results = $results->indexBy('slug');

                if (!$style || !$maxFont || !$minFont) {
                    return $results;
                }

                //Highest and lowest numbers of occurrences and their difference
                $maxCount = $results->first()->post_count;
                $minCount = $results->last()->post_count;
                $diffCount = $maxCount - $minCount;
                $diffFont = $maxFont - $minFont;

                return $results->map(function ($value) use ($minCount, $diffCount, $maxFont, $minFont, $diffFont) {
                    if ($diffCount) {
                        $value->size = round((($value->post_count - $minCount) / $diffCount * $diffFont) + $minFont);
                    } else {
                        $value->size = $maxFont;
                    }

                    return $value;
                });
            })
            ->cache($cache, $this->Tags->Posts->getCacheName())
            ->all();

        if ($shuffle) {
            $tags = $tags->shuffle();
        }

        $this->set(compact('prefix', 'tags'));
    }
}
