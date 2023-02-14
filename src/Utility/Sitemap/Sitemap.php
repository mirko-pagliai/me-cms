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

namespace MeCms\Utility\Sitemap;

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use MeCms\Utility\StaticPage;

/**
 * This class contains methods called by the `SitemapBuilder`.
 * Each method must be return an array or urls to add to the sitemap.
 *
 * This class contains methods that will be called automatically. You do not need to call these methods manually.
 * @see \MeCms\Utility\Sitemap\SitemapBuilder
 */
class Sitemap extends SitemapBase
{
    /**
     * Returns pages urls
     * @return array
     */
    public static function pages(): array
    {
        if (!getConfig('sitemap.pages')) {
            return [];
        }

        /** @var \MeCms\Model\Table\PagesCategoriesTable $Table */
        $Table = TableRegistry::getTableLocator()->get('MeCms.PagesCategories');

        $result = Cache::read('sitemap', $Table->getCacheName());
        if (!$result) {
            $categories = $Table->find('active')
                ->select(['id', 'lft', 'slug'])
                ->contain($Table->Pages->getAlias(), fn(Query $query): Query => $query->find('active')->select(['category_id', 'slug', 'modified'])->orderDesc('modified'))
                ->orderAsc($Table->getAlias() . '.lft')
                ->all();

            if ($categories->isEmpty()) {
                return [];
            }

            //Adds categories index, then adds each category and each page for each category
            $result = [self::parse(['_name' => 'pagesCategories'])];
            foreach ($categories as $Category) {
                $result[] = self::parse(
                    ['_name' => 'pagesCategory', $Category->get('slug')],
                    ['lastmod' => array_value_first($Category->get('pages'))->get('modified')]
                );

                foreach ($Category->get('pages') as $Page) {
                    $result[] = self::parse(['_name' => 'page', $Page->get('slug')], ['lastmod' => $Page->get('modified')]);
                }
            }

            Cache::write('sitemap', $result, $Table->getCacheName());
        }

        return $result;
    }

    /**
     * Returns posts urls
     * @return array
     */
    public static function posts(): array
    {
        if (!getConfig('sitemap.posts')) {
            return [];
        }

        /** @var \MeCms\Model\Table\PostsCategoriesTable $Table */
        $Table = TableRegistry::getTableLocator()->get('MeCms.PostsCategories');

        $result = Cache::read('sitemap', $Table->getCacheName());
        if (!$result) {
            $categories = $Table->find('active')
                ->select(['id', 'lft', 'slug'])
                ->contain($Table->Posts->getAlias(), fn(Query $query): Query => $query->find('active')->select(['category_id', 'slug', 'modified'])->orderDesc('modified'))
                ->orderAsc($Table->getAlias() . '.lft')
                ->all();

            if ($categories->isEmpty()) {
                return [];
            }

            //Adds posts index, categories index and posts search, then adds each category and each post for each category
            /** @var \MeCms\Model\Entity\Post $Latest */
            $Latest = $Table->Posts->find('active')->select(['modified'])->orderDesc('modified')->firstOrFail();
            $result = [
                self::parse(['_name' => 'posts'], ['lastmod' => $Latest->get('modified')]),
                self::parse(['_name' => 'postsCategories']),
                self::parse(['_name' => 'postsSearch'], ['priority' => '0.2']),
            ];
            foreach ($categories as $Category) {
                $result[] = self::parse(
                    ['_name' => 'postsCategory', $Category->get('slug')],
                    ['lastmod' => array_value_first($Category->get('posts'))->get('modified')]
                );

                foreach ($Category->get('posts') as $Post) {
                    $result[] = self::parse(['_name' => 'post', $Post->get('slug')], ['lastmod' => $Post->get('modified')]);
                }
            }

            Cache::write('sitemap', $result, $Table->getCacheName());
        }

        return $result;
    }

    /**
     * Returns posts tags urls
     * @return array
     */
    public static function postsTags(): array
    {
        if (!getConfig('sitemap.posts_tags')) {
            return [];
        }

        /** @var \MeCms\Model\Table\TagsTable $Table */
        $Table = TableRegistry::getTableLocator()->get('MeCms.Tags');

        $result = Cache::read('sitemap', $Table->getCacheName());
        if (!$result) {
            $tags = $Table->find('active')
                ->select(['tag', 'modified'])
                ->orderAsc($Table->getAlias() . '.tag')
                ->all();

            if ($tags->isEmpty()) {
                return [];
            }

            //Adds tags index, then adds each tag
            /** @var \MeCms\Model\Entity\Tag $Latest */
            $Latest = $Table->find()->select(['modified']) ->orderDesc('modified')->firstOrFail();
            $result = [self::parse(['_name' => 'postsTags'], ['lastmod' => $Latest->get('modified')])];
            foreach ($tags as $Tag) {
                $result[] = self::parse(['_name' => 'postsTag', $Tag->get('slug')], ['lastmod' => $Tag->get('modified')]);
            }

            Cache::write('sitemap', $result, $Table->getCacheName());
        }

        return $result;
    }

    /**
     * Returns static pages urls
     * @return array
     * @throws \ErrorException
     */
    public static function staticPages(): array
    {
        if (!getConfig('sitemap.static_pages')) {
            return [];
        }

        return StaticPage::all()->map(fn(Entity $Page): array => self::parse(['_name' => 'page', $Page->get('slug')], ['lastmod' => $Page->get('modified')]))->toArray();
    }

    /**
     * Returns systems urls
     * @return array
     */
    public static function systems(): array
    {
        if (!getConfig('sitemap.systems') || !getConfig('default.contact_us')) {
            return [];
        }

        return [self::parse(['_name' => 'contactUs'])];
    }
}
