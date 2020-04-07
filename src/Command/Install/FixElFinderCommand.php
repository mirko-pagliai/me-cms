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
use MeTools\Console\Command;

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
     * Fixes ElFinder
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $file = ELFINDER . 'php' . DS . 'connector.minimal.php';
        if ($this->verboseIfFileExists($io, $file)) {
            return null;
        }

        $uploads = add_slash_term(UPLOADED);
        $url = Router::url('/files', true);
$str = <<<HEREDOC
<?php
error_reporting(0); // Set E_ALL for debuging

session_start();

if (!isset(\$_SESSION['Auth']['User']['id'])) {
    header("HTTP/1.0 401 Unauthorized");
    echo '{"error": "Login failed."}';
    exit;
}

is_readable('./vendor/autoload.php') && require './vendor/autoload.php';
require './autoload.php';

function access(\$attr, \$path, \$data, \$volume, \$isDir, \$relpath) {
    \$basename = basename(\$path);
    return \$basename[0] === '.'                  // if file/folder begins with '.' (dot)
             && strlen(\$relpath) !== 1           // but with out volume root
        ? !(\$attr == 'read' || \$attr == 'write') // set read+write to false, other (locked+hidden) set to true
        :  null;                                 // else elFinder decide it itself
}

\$opts = array(
    'roots'  => array(
        array(
            'driver' => 'LocalFileSystem',
            'path'   => '{$uploads}',
            'URL'    => '{$url}/',
            'trashHash'     => 't1_Lw',                         // elFinder's hash of trash folder
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/',     // to make hash same to Linux one on windows too
            'uploadDeny'    => array('all'),                    // All Mimetypes not allowed to upload
            'uploadAllow'   => array('image', 'text/plain'),    // Mimetype `image` and `text/plain` allowed to upload
            'uploadOrder'   => array('deny', 'allow'),          // allowed Mimetype `image` and `text/plain` only
            'accessControl' => 'access',                        // disable and hide dot starting files (OPTIONAL)
        ),
        // Trash volume
        array(
            'id'            => '1',
            'driver'        => 'Trash',
            'path'          => '{$uploads}.trash/',
            'tmbURL'        => '{$url}/.trash/.tmb/',
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
            'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
            'uploadAllow'   => array('image', 'text/plain'), // Same as above
            'uploadOrder'   => array('deny', 'allow'),      // Same as above
            'accessControl' => 'access',                    // Same as above
        ),
    )
);

\$connector = new elFinderConnector(new elFinder(\$opts));
\$connector->run();

HEREDOC;

        $io->createFile($file, $str);

        return null;
    }
}
