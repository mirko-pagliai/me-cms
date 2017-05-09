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
namespace MeCms\Test\TestCase\Controller\Traits;

use Cake\TestSuite\TestCase;
use MeCms\Controller\PostsController;
use Reflection\ReflectionTrait;

/**
 * GetStartAndEndDateTraitTest class
 */
class GetStartAndEndDateTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests for `getStartAndEndDate()` method
     * @test
     */
    public function testGetStartAndEndDate()
    {
        $controller = new PostsController;

        //"today" special word
        list($start, $end) = $this->invokeMethod($controller, 'getStartAndEndDate', ['today']);
        $this->assertEquals(date('Y-m-d') . ' 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals(date('Y-m-d', time() + DAY) . ' 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //"yesterday" special word
        list($start, $end) = $this->invokeMethod($controller, 'getStartAndEndDate', ['yesterday']);
        $this->assertEquals(date('Y-m-d', time() - DAY) . ' 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals(date('Y-m-d') . ' 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //Only year
        list($start, $end) = $this->invokeMethod($controller, 'getStartAndEndDate', ['2017']);
        $this->assertEquals('2017-01-01 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals('2018-01-01 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //only year and month
        list($start, $end) = $this->invokeMethod($controller, 'getStartAndEndDate', ['2017/04']);
        $this->assertEquals('2017-04-01 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals('2017-05-01 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //Full date
        list($start, $end) = $this->invokeMethod($controller, 'getStartAndEndDate', ['2017/04/15']);
        $this->assertEquals('2017-04-15 00:00:00', $start->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals('2017-04-16 00:00:00', $end->i18nFormat('yyyy-MM-dd HH:mm:ss'));
    }
}
