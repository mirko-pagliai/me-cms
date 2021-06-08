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

namespace MeCms\Test\TestCase\Template\Element;

use MeCms\TestSuite\TestCase;
use MeCms\View\View\AppView;
use Tools\Filesystem;

/**
 * TopbarTest class
 */
class TopbarTest extends TestCase
{
    /**
     * Test for `topbar` element
     * @test
     */
    public function testTopbar(): void
    {
        $View = new AppView();
        $result = $View->element('MeCms.topbar');
        $this->assertStringContainsString('<a href="/" class="nav-link" title="Home"><i class="fas fa-home"> </i> Home</a>', $result);

        $this->loadPlugins(['TestPlugin']);
        $result = $View->element('MeCms.topbar');
        $this->assertStringContainsString('<a href="/" class="nav-link" title="Home"><i class="fas fa-home"> </i> Home</a>', $result);
        $this->assertStringContainsString('<a href="http://localhost" title="A link from plugin">A link from plugin</a>', $result);
    }

    /**
     * Test for `topbar` element, using the APP helper
     * @test
     */
    public function testTopbarFromApp(): void
    {
        $content = <<<HEREDOC
<?php
namespace App\View\Helper;

use Cake\View\Helper;

class TopbarHelper extends Helper
{
    public \$helpers = ['Html' => ['className' => 'MeTools.Html']];

    public function build(): array
    {
        return [\$this->Html->link('A link from APP', 'http://localhost')];
    }
}
HEREDOC;
        $file = TEST_APP . 'TestApp' . DS . 'View' . DS . 'Helper' . DS . 'TopbarHelper.php';
        (new Filesystem())->createFile($file, $content);
        require_once $file;
        $result = (new AppView())->element('MeCms.topbar');
        unlink($file);
        $this->assertStringContainsString('<a href="http://localhost" title="A link from APP">A link from APP</a>', $result);
    }
}
