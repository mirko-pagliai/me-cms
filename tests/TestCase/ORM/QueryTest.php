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

namespace MeCms\Test\TestCase\ORM;

use Cake\Cache\Cache;
use MeCms\ORM\Query;
use MeCms\TestSuite\TestCase;
use RuntimeException;

/**
 * QueryTest class
 */
class QueryTest extends TestCase
{
    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Core/Articles',
        'plugin.MeCms.Posts',
    ];

    /**
     * @uses \MeCms\ORM\Query::cache()
     * @test
     */
    public function testQuery(): void
    {
        //This table HAS the `getCacheName()` method, so its value will be used as default
        $Table = $this->getTable('MeCms.Posts');
        $expectedConfig = $Table->getCacheName();
        $cacheKey = 'aKey';
        /** @var \Cake\Cache\Engine\FileEngine $defaultEngine */
        $defaultEngine = Cache::pool('default');

        $Query = $this->getMockBuilder(Query::class)
            ->setConstructorArgs([$Table->getConnection(), $Table])
            ->onlyMethods(['_cache'])
            ->getMock();

        $matcher = $this->exactly(4);
        $Query->expects($matcher)
            ->method('_cache')
            ->with($this->equalTo($cacheKey), $this->callback(function ($config) use ($matcher, $expectedConfig, $defaultEngine): bool {
                switch ($matcher->getInvocationCount()) {
                    case 1:
                    case 3:
                        return $config === $expectedConfig;
                    case 2:
                        return $config === 'default';
                    case 4:
                        return $config === $defaultEngine;
                    default:
                        return false;
                }
            }));

        $Query->cache($cacheKey);
        $Query->cache($cacheKey, 'default');
        $Query->cache($cacheKey, $expectedConfig);
        $Query->cache($cacheKey, $defaultEngine);

        //This table DOES NOT have the `getCacheName()` method, so the `default` value will be used as default
        /** @var \App\Model\Table\ArticlesTable&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('Articles');

        $Query = $this->getMockBuilder(Query::class)
            ->setConstructorArgs([$Table->getConnection(), $Table])
            ->onlyMethods(['_cache'])
            ->getMock();

        $matcher = $this->exactly(4);
        $Query->expects($matcher)
            ->method('_cache')
            ->with($this->equalTo($cacheKey), $this->callback(function ($config) use ($matcher, $defaultEngine): bool {
                switch ($matcher->getInvocationCount()) {
                    case 1:
                    case 2:
                        return $config === 'default';
                    case 3:
                        return $config === 'articles';
                    case 4:
                        return $config === $defaultEngine;
                    default:
                        return false;
                }
            }));

        $Query->cache($cacheKey);
        $Query->cache($cacheKey, 'default');
        $Query->cache($cacheKey, 'articles');
        $Query->cache($cacheKey, $defaultEngine);

        $this->expectException(RuntimeException::class);
        $Query->update();
        $Query->cache($cacheKey);
    }
}
