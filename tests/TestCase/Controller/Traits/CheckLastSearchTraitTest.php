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

use Cake\Core\Configure;
use MeCms\Controller\AppController;
use MeCms\Controller\Traits\CheckLastSearchTrait;
use MeCms\TestSuite\TestCase;
use Tools\TestSuite\ReflectionTrait;

/**
 * CheckLastSearchTraitTest class
 */
class CheckLastSearchTraitTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @uses \MeCms\Controller\Traits\CheckLastSearchTrait::checkLastSearch()
     * @test
     */
    public function testCheckLastSearch(): void
    {
        $Controller = new class extends AppController {
            use CheckLastSearchTrait;
        };
        $checkLastSearchMethod = fn($queryId = false): bool => $this->invokeMethod($Controller, 'checkLastSearch', [$queryId]);

        $this->assertTrue($checkLastSearchMethod('my-query'));
        $firstSession = $Controller->getRequest()->getSession()->read('last_search.id');
        $this->assertEquals('6bd2aab45de1d380f1e47e147494dbbd', $firstSession);

        //Tries with the same query
        $this->assertTrue($checkLastSearchMethod('my-query'));
        $secondSession = $Controller->getRequest()->getSession()->read('last_search.id');
        $this->assertEquals('6bd2aab45de1d380f1e47e147494dbbd', $secondSession);

        $this->assertEquals($firstSession, $secondSession);

        //Tries with another query
        $this->assertFalse($checkLastSearchMethod('another-query'));
        $thirdSession = $Controller->getRequest()->getSession()->read('last_search.id');
        $this->assertEquals($firstSession, $thirdSession);

        //Deletes the session and tries again with another query
        $Controller->getRequest()->getSession()->delete('last_search');
        $this->assertTrue($checkLastSearchMethod('another-query'));
        $fourthSession = $Controller->getRequest()->getSession()->read('last_search.id');
        $this->assertNotEquals($firstSession, $fourthSession);

        foreach ([0, false] as $value) {
            $Controller->getRequest()->getSession()->delete('last_search');
            Configure::write('MeCms.security.search_interval', $value);
            $this->assertTrue($checkLastSearchMethod());
            $this->assertNull($Controller->getRequest()->getSession()->read('last_search.id'));
        }
    }
}
