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
 * @since       2.29.0
 */

namespace MeCms\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Routing\Router;
use MeCms\Core\Plugin;
use MeTools\Console\Command;
use Tools\Exception\NotReadableException;
use Tools\Filesystem;

/**
 * Fixes ElFinder
 */
class FixElFinderCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Fixes {0}', 'ElFinder'));
    }

    /**
     * Internal method to create the `php/connector.minimal.php` file
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     * @since 2.29.2
     * @throws \Tools\Exception\FileNotExistsException
     * @throws \Tools\Exception\NotReadableException
     */
    protected function createConnectorMinimal(ConsoleIo $io): void
    {
        $target = ELFINDER . 'php' . DS . 'connector.minimal.php';
        if ($this->verboseIfFileExists($io, $target)) {
            return;
        }

        $origin = Plugin::path('MeCms', 'config' . DS . 'elfinder' . DS . 'connector.minimal.php');
        $content = str_replace(['{{UPLOADS_PATH}}', '{{UPLOADS_URL}}'], [(new Filesystem())->addSlashTerm(UPLOADED), Router::url('/files', true)], file_get_contents($origin) ?: '');
        $io->createFile($target, $content);
    }

    /**
     * Internal method to create the `elfinder-cke.html` file
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     * @since 2.29.2
     * @throws \Tools\Exception\FileNotExistsException
     * @throws \Tools\Exception\NotReadableException
     */
    protected function createElfinderCke(ConsoleIo $io): void
    {
        $target = ELFINDER . 'elfinder-cke.html';
        if ($this->verboseIfFileExists($io, $target)) {
            return;
        }

        $origin = Plugin::path('MeCms', 'config' . DS . 'elfinder' . DS . 'elfinder-cke.html');
        $io->createFile($target, file_get_contents($origin) ?: '');
    }

    /**
     * Fixes ElFinder
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        try {
            $this->createConnectorMinimal($io);
            $this->createElfinderCke($io);
        } catch (NotReadableException $e) {
            return $io->error($e->getMessage());
        }

        return null;
    }
}
