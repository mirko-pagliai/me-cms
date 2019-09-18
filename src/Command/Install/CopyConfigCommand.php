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
namespace MeCms\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use MeCms\Core\Plugin;
use MeTools\Console\Command;

/**
 * Copies the configuration files
 */
class CopyConfigCommand extends Command
{
    /**
     * Configuration files to be copied
     */
    const CONFIG_FILES = [
        'MeCms.recaptcha',
        'MeCms.me_cms',
        'MeCms.widgets',
    ];

    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        return $parser->setDescription(__d('me_cms', 'Copies the configuration files'));
    }

    /**
     * Copies the configuration files
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $class = get_class($this);
        foreach ($class::CONFIG_FILES as $file) {
            list($plugin, $file) = pluginSplit($file);
            $this->copyFile(
                $io,
                Plugin::path($plugin, 'config' . DS . $file . '.php'),
                add_slash_term(CONFIG) . $file . '.php'
            );
        }

        return null;
    }
}
