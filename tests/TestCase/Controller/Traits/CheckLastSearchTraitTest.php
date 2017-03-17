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

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use MeCms\Controller\PostsController;
use Reflection\ReflectionTrait;

/**
 * CheckLastSearchTraitTest class
 */
class CheckLastSearchTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests for `checkLastSearch()` method
     * @test
     */
    public function testCheckLastSearch()
    {
        $controller = new PostsController;

        $this->assertTrue($this->invokeMethod($controller, 'checkLastSearch', ['my-query']));
        $firstSession = $controller->request->session()->read('last_search');
        $this->assertNotEmpty($firstSession);
        $this->assertEquals('6bd2aab45de1d380f1e47e147494dbbd', $firstSession['id']);

        //Tries with the same query
        $this->assertTrue($this->invokeMethod($controller, 'checkLastSearch', ['my-query']));
        $secondSession = $controller->request->session()->read('last_search');
        $this->assertNotEmpty($secondSession);
        $this->assertEquals('6bd2aab45de1d380f1e47e147494dbbd', $secondSession['id']);

        $this->assertEquals($firstSession, $secondSession);

        //Tries with another query
        $this->assertFalse($this->invokeMethod($controller, 'checkLastSearch', ['another-query']));
        $thirdSession = $controller->request->session()->read('last_search');
        $this->assertEquals($firstSession, $thirdSession);

        //Deletes the session and tries again with another query
        $controller->request->session()->delete('last_search');
        $this->assertTrue($this->invokeMethod($controller, 'checkLastSearch', ['another-query']));
        $fourthSession = $controller->request->session()->read('last_search');
        $this->assertNotEquals($firstSession, $fourthSession);

        foreach ([0, false] as $value) {
            $controller->request->session()->delete('last_search');
            Configure::write(ME_CMS . '.security.search_interval', $value);

            $this->assertTrue($this->invokeMethod($controller, 'checkLastSearch'));
            $this->assertNull($controller->request->session()->read('last_search'));
        }
    }

}
