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
 * @since       2.26.0
 */
namespace MeCms\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Filesystem\Folder;
use MeCms\Utility\Checkups\KCFinder;
use MeTools\Console\Command;

/**
 * Fixes KCFinder
 */
class FixKcfinderCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        return $parser->setDescription(__d('me_cms', 'Fixes {0}', 'KCFinder'));
    }

    /**
     * Fixes KCFinder
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $file = Folder::slashTerm(WWW_ROOT) . 'vendor' . DS . 'kcfinder' . DS . '.htaccess';

        if ($this->verboseIfFileExists($io, $file)) {
        } elseif ((new KCFinder())->isAvailable()) {
            $io->createFile(
                Folder::slashTerm(WWW_ROOT) . 'vendor' . DS . 'kcfinder' . DS . '.htaccess',
                'php_value session.cache_limiter must-revalidate' . PHP_EOL .
                'php_value session.cookie_httponly On' . PHP_EOL .
                'php_value session.cookie_lifetime 14400' . PHP_EOL .
                'php_value session.gc_maxlifetime 14400' . PHP_EOL .
                'php_value session.name CAKEPHP'
            );
        } else {
            $io->error(__d('me_tools', '{0} is not available', 'KCFinder'));
        }

        return null;
    }
}
