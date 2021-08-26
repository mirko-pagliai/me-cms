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

namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\TestSuite\EntityTestCase;

/**
 * PostsTagTest class
 */
class PostsTagTest extends EntityTestCase
{
    /**
     * Test for fields that cannot be mass assigned
     * @test
     */
    public function testNoAccessibleProperties(): void
    {
        $this->assertHasNoAccessibleProperty('id');
    }
}
