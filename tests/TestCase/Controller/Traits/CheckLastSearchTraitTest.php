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
