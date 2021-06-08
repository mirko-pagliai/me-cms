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
 * @since       2.25.4
 */

namespace MeCms\TestSuite;

use MeCms\TestSuite\EntityTestCase;

/**
 * Abstract class for `PageTest` and `PostTest` classes
 */
abstract class PostAndPageEntityTestCase extends EntityTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Entity->set(['slug' => 'a-slug', 'text' => '<b>A</b> [readmore /]text']);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @return void
     * @test
     */
    public function testNoAccessibleProperties(): void
    {
        $this->assertHasNoAccessibleProperty(['id', 'preview', 'modified']);
    }

    /**
     * Test for `_getPlainText()` method
     * @return void
     * @test
     */
    public function testPlainTextGetMutator(): void
    {
        $this->assertEquals('A text', $this->Entity->get('plain_text'));
    }

    /**
     * Test for `_getText()` method
     * @return void
     * @test
     */
    public function testTextGetMutator(): void
    {
        $this->assertEquals('<b>A</b> <!-- read-more -->text', $this->Entity->get('text'));
    }

    /**
     * Test for `_getUrl()` method
     * @return void
     * @test
     */
    abstract public function testUrl();
}
