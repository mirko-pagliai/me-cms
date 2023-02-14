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
 * @since       2.30.0
 */

namespace MeCms\Utility\Sitemap;

use Cake\Routing\Router;

/**
 * Abstract base class for each `Sitemap` class
 */
abstract class SitemapBase
{
    /**
     * Internal method to parse each url
     * @param string|array|null $url Url
     * @param array<array-key, mixed> $options Options. Valid options are `lastmod` and `priority`
     * @return array{loc: string, lastmod?: string, priority: string}
     */
    protected static function parse($url, array $options = []): array
    {
        $result['loc'] = Router::url($url, true);

        if (isset($options['lastmod'])) {
            /** @var string $lastmod */
            $lastmod = is_string($options['lastmod']) ? $options['lastmod'] : $options['lastmod']->format('c');
            $result += compact('lastmod');
        }
        /** @var string $priority */
        $priority = $options['priority'] ?? '0.5';

        return $result + compact( 'priority');
    }
}
