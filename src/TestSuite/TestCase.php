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
 * @since       2.26.0
 */
namespace MeCms\TestSuite;

use MeTools\TestSuite\TestCase as BaseTestCase;

/**
 * TestCase class
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadPlugins(['MeCms']);
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        safe_rmdir_recursive(KCFINDER);
        safe_unlink_recursive(WWW_ROOT . 'vendor', 'empty');
    }
}
