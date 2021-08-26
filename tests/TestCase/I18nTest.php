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

namespace MeCms\Test\TestCase;

use Cake\I18n\I18n;
use MeCms\TestSuite\TestCase;

/**
 * I18nTest class
 */
class I18nTest extends TestCase
{
    /**
     * Tests that the constants defined in `config/i18n_constants.php` are
     *  translated correctly
     * @test
     */
    public function testI18nConstant(): void
    {
        $translator = I18n::getTranslator('me_cms', 'it');
        $this->assertEquals('Aggiungi', $translator->translate(I18N_ADD));
    }
}
