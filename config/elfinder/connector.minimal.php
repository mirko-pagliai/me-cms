<?php
error_reporting(0); //Set E_ALL for debuging

session_start();

if (!isset($_SESSION['Auth']['User']['id'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo '{"error": "Login failed."}';
    exit;
}

is_readable('./vendor/autoload.php') && require './vendor/autoload.php';
require './autoload.php';

/**
 * Control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 * @param string $attr attribute name (read|write|locked|hidden)
 * @param string $path absolute file path
 * @param string $data value of volume option `accessControlData`
 * @param object $volume elFinder volume driver object
 * @param bool|null $isDir path is directory (true: directory, false: file, null: unknown)
 * @param string $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 */
function access($attr, $path, $data, $volume, $isDir, $relpath)
{
    $basename = basename($path);

    return $basename[0] === '.' // if file/folder begins with '.' (dot)
             && strlen($relpath) !== 1 // but with out volume root
        ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
        : null; //else elFinder decide it itself
}

$opts = [
    'roots' => [
        [
            'driver' => 'LocalFileSystem',
            'path' => '{{UPLOADS_PATH}}',
            'URL' => '{{UPLOADS_URL}}',
            'trashHash' => 't1_Lw', // elFinder's hash of trash folder
            'winHashFix' => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
            'uploadDeny' => ['all'], // All Mimetypes not allowed to upload
            'uploadAllow' => ['image', 'text/plain'], // Mimetype `image` and `text/plain` allowed to upload
            'uploadOrder' => ['deny', 'allow'], // allowed Mimetype `image` and `text/plain` only
            'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
        ],
        // Trash volume
        [
            'id' => '1',
            'driver' => 'Trash',
            'path' => '{{UPLOADS_PATH}}.trash',
            'tmbURL' => '{{UPLOADS_URL}}/.trash/.tmb/',
            'winHashFix' => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
            'uploadDeny' => ['all'], // Recomend the same settings as the original volume that uses the trash
            'uploadAllow' => ['image', 'text/plain'], // Same as above
            'uploadOrder' => ['deny', 'allow'], // Same as above
            'accessControl' => 'access', // Same as above
        ],
    ],
];

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
