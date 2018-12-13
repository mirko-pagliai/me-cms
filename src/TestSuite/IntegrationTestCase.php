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
namespace MeCms\TestSuite;

use Cake\Core\Configure;
use Cake\Http\BaseApplication;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * A test case class intended to make integration tests of your controllers
 *  easier.
 *
 * This test class provides a number of helper methods and features that make
 *  dispatching requests and checking their responses simpler. It favours full
 *  integration tests over mock objects as you can test more of your code
 *  easily and avoid some of the maintenance pitfalls that mock objects create.
 */
abstract class IntegrationTestCase extends TestCase
{
    use IntegrationTestTrait {
        IntegrationTestTrait::setUp as IntegrationTestTraitSetUp;
        IntegrationTestTrait::tearDown as IntegrationTestTraitTearDown;
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->IntegrationTestTraitSetUp();

        $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);
        $app->addPlugin('MeCms')->pluginBootstrap();

        Configure::write('DatabaseBackup.mailSender', getConfigOrFail('email.webmaster'));
    }
}
