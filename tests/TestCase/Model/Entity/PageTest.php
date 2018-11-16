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
namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\TestSuite\EntityTestCase;

/**
 * PageTest class
 */
class PageTest extends EntityTestCase
{
    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'preview', 'modified']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField('plain_text');
    }

    /**
     * Test for `_getPlainText()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertNull($this->Entity->plain_text);

        $expected = 'This is a text';

        $this->Entity->text = 'This is a [readmore /]text';
        $this->assertEquals($expected, $this->Entity->plain_text);
        $this->assertNotEquals($this->Entity->text, $this->Entity->plain_text);

        $this->Entity->text = $expected;
        $this->assertEquals($expected, $this->Entity->plain_text);
        $this->assertEquals($this->Entity->text, $this->Entity->plain_text);
    }
}
