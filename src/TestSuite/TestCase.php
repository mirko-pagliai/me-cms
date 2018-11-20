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

use Cake\Core\Configure;
use Cake\Http\BaseApplication;
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
        $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);
        $app->addPlugin('MeCms')->pluginBootstrap();

        parent::setUp();

        Configure::write(DATABASE_BACKUP . '.mailSender', getConfigOrFail('email.webmaster'));
    }
}
