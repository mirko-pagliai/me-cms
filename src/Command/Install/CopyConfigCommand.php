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
use Cake\Core\Configure;
use MeCms\Core\Plugin;
use MeTools\Console\Command;
use Tools\Filesystem;

/**
 * Copies the configuration files
 */
class CopyConfigCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Copies the configuration files'));
    }

    /**
     * Copies the configuration files
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     * @throws \Tools\Exception\FileNotExistsException|\ErrorException
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $Filesystem = new Filesystem();
        foreach (array_unique(Configure::readOrFail('CONFIG_FILES')) as $file) {
            [$plugin, $file] = pluginSplit($file);
            $file .= '.php';
            $this->copyFile($io, Plugin::path($plugin, 'config' . DS . $file), $Filesystem->concatenate(CONFIG, $file));
        }

        return null;
    }
}
