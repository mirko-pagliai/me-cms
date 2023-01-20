<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

namespace MeCms\Test\TestCase\Controller\Traits;

use MeCms\Controller\AppController;
use MeCms\Controller\Traits\GetStartAndEndDateTrait;
use MeCms\TestSuite\TestCase;

/**
 * GetStartAndEndDateTraitTest class
 */
class GetStartAndEndDateTraitTest extends TestCase
{
    /**
     * @uses \MeCms\Controller\Traits\GetStartAndEndDateTrait::getStartAndEndDate()
     * @test
     */
    public function testGetStartAndEndDate(): void
    {
        $Controller = new class extends AppController {
            use GetStartAndEndDateTrait;
        };
        $getStartAndEndDateMethod = fn(string $date): array => $this->invokeMethod($Controller, 'getStartAndEndDate', [$date]);

        //"today" special word
        [$start, $end] = $getStartAndEndDateMethod('today');
        $this->assertEquals(date('Y-m-d') . ' 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals(date('Y-m-d', time() + DAY) . ' 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //"yesterday" special word
        [$start, $end] = $getStartAndEndDateMethod('yesterday');
        $this->assertEquals(date('Y-m-d', time() - DAY) . ' 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals(date('Y-m-d') . ' 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //Only year
        [$start, $end] = $getStartAndEndDateMethod('2017');
        $this->assertEquals('2017-01-01 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals('2018-01-01 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //only year and month
        [$start, $end] = $getStartAndEndDateMethod('2017/04');
        $this->assertEquals('2017-04-01 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals('2017-05-01 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //Full date
        [$start, $end] = $getStartAndEndDateMethod('2017/04/15');
        $this->assertEquals('2017-04-15 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals('2017-04-16 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));
    }
}
