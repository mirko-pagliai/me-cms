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
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @return void
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'preview', 'modified']);
    }

    /**
     * Test for virtual fields
     * @return void
     * @test
     */
    abstract public function testVirtualFields();

    /**
     * Test for `_getPlainText()` method
     * @return void
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertNull($this->Entity->plain_text);

        $this->assertEquals('This is a text', $this->Entity->set('text', 'This is a [readmore /]text')->get('plain_text'));
    }
}
