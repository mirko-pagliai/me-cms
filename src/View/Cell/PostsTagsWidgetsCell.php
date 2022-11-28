<?php
declare(strict_types=1);

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

use Cake\Collection\CollectionInterface;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\View\Cell;
use MeCms\Model\Entity\Tag;
use Tools\Exceptionist;

/**
 * PostsTagsWidgets cell
 */
class PostsTagsWidgetsCell extends Cell
{
    /**
     * @var \Cake\ORM\Table
     */
    protected Table $Tags;

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        $this->Tags = $this->fetchTable('MeCms.Tags');
    }

    /**
     * Internal method to get the font sizes
     * @param array<string, int>|bool $style Style for tags. Array with `maxFont` and `minFont` keys or `false` to disable
     * @return array
     * @throws \ErrorException
     */
    protected function getFontSizes($style = []): array
    {
        //Maximum and minimum font sizes we want to use
        $maxFont = is_array($style) && array_key_exists('maxFont', $style) ? $style['maxFont'] : 40;
        $minFont = is_array($style) && array_key_exists('minFont', $style) ? $style['minFont'] : 12;
        Exceptionist::isTrue($maxFont > $minFont, __d('me_cms', 'Invalid values'));

        return [$maxFont, $minFont];
    }

    /**
     * Popular tags widgets
     * @param int $limit Limit
     * @param string $prefix Prefix for each tag. This works only with the cloud
     * @param string $render Render type (`cloud`, `form` or `list`)
     * @param bool $shuffle Shuffles tags
     * @param array|bool $style Style for tags. Array with `maxFont` and `minFont` keys or `false` to disable
     * @return void
     * @throws \ErrorException
     */
    public function popular(
        int $limit = 10,
        string $prefix = '#',
        string $render = 'cloud',
        bool $shuffle = true,
        $style = ['maxFont' => 40, 'minFont' => 12]
    ): void {
        $this->viewBuilder()->setTemplate(sprintf('popular_as_%s', $render));

        //Returns on tags index
        if ($this->request->is('url', ['_name' => 'postsTags'])) {
            return;
        }

        //Sets default maximum and minimum font sizes we want to use
        $maxFont = $minFont = 0;

        //Sets the initial cache name
        $cache = sprintf('widget_tags_popular_%s', $limit);

        if ($style && is_array($style)) {
            //Updates maximum and minimum font sizes we want to use
            [$maxFont, $minFont] = $this->getFontSizes($style);

            $cache = sprintf('%s_max_%s_min_%s', $cache, $maxFont, $minFont);
        }

        $tags = $this->Tags->find()
            ->select(['tag', 'post_count'])
            ->limit($limit)
            ->order(['post_count' => 'DESC', 'tag' => 'ASC'])
            ->formatResults(function (ResultSet $results) use ($style, $maxFont, $minFont): CollectionInterface {
                $results = $results->indexBy('slug');

                if (!$results->count() || !$style || !$maxFont || !$minFont) {
                    return $results;
                }

                //Highest and lowest numbers of occurrences and their difference
                $minCount = $results->last()->get('post_count');
                $diffCount = $results->first()->get('post_count') - $minCount;
                $diffFont = $maxFont - $minFont;

                return $results->map(fn(Tag $tag): Tag => $tag->set('size', $diffCount ? round((($tag->get('post_count') - $minCount) / $diffCount * $diffFont) + $minFont) : $maxFont));
            })
            ->cache($cache)
            ->all();

        if ($shuffle) {
            $tags = $tags->shuffle();
        }

        $this->set(compact('prefix', 'tags'));
    }
}
