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
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Core/Articles',
        'plugin.MeCms.Posts',
    ];

    /**
     * Test for `query()` method
     * @test
     */
    public function testQuery(): void
    {
        //This table HAS the `getCacheName()` method.
        //So its value will be used as default
        $Table = $this->getTable('MeCms.Posts', []);
        $expectedConfig = $Table->getCacheName();
        $cacheKey = 'aKey';
        /** @var \Cake\Cache\Engine\FileEngine $defaultEngine */
        $defaultEngine = Cache::pool('default');

        $Query = $this->getMockBuilder(Query::class)
            ->setConstructorArgs([$Table->getConnection(), $Table])
            ->setMethods(['_cache'])
            ->getMock();

        $Query->expects($this->exactly(4))
            ->method('_cache')
            ->withConsecutive(
                [$this->equalTo($cacheKey), $this->equalTo($expectedConfig)],
                [$this->equalTo($cacheKey), $this->equalTo('default')],
                [$this->equalTo($cacheKey), $this->equalTo($expectedConfig)],
                [$this->equalTo($cacheKey), $this->equalTo($defaultEngine)]
            );

        $Query->cache($cacheKey);
        $Query->cache($cacheKey, 'default');
        $Query->cache($cacheKey, $expectedConfig);
        $Query->cache($cacheKey, $defaultEngine);

        //This table DOES NOT have the `getCacheName()` method.
        //So the `default` value will be used as default
        /** @var \App\Model\Table\ArticlesTable&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('Articles', []);

        $Query = $this->getMockBuilder(Query::class)
            ->setConstructorArgs([$Table->getConnection(), $Table])
            ->setMethods(['_cache'])
            ->getMock();

        $Query->expects($this->exactly(4))
            ->method('_cache')
            ->withConsecutive(
                [$this->equalTo($cacheKey), $this->equalTo('default')],
                [$this->equalTo($cacheKey), $this->equalTo('default')],
                [$this->equalTo($cacheKey), $this->equalTo('articles')],
                [$this->equalTo($cacheKey), $this->equalTo($defaultEngine)]
            );

        $Query->cache($cacheKey);
        $Query->cache($cacheKey, 'default');
        $Query->cache($cacheKey, 'articles');
        $Query->cache($cacheKey, $defaultEngine);

        $this->expectException(RuntimeException::class);
        $Query->update();
        $Query->cache($cacheKey);
    }
}
