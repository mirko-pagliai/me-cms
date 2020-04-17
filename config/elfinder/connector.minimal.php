<?php
error_reporting(0); // Set E_ALL for debuging

session_start();

if (!isset($_SESSION['Auth']['User']['id'])) {
    header("HTTP/1.0 401 Unauthorized");
    echo '{"error": "Login failed."}';
    exit;
}

is_readable('./vendor/autoload.php') && require './vendor/autoload.php';
require './autoload.php';

function access($attr, $path, $data, $volume, $isDir, $relpath) {
    $basename = basename($path);
    return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
             && strlen($relpath) !== 1           // but with out volume root
        ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
        :  null;                                 // else elFinder decide it itself
}

$opts = array(
    'roots'  => array(
        array(
            'driver' => 'LocalFileSystem',
            'path'   => '{{UPLOADS_PATH}}',
            'URL'    => '{{UPLOADS_URL}}',
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
            'path'          => '{{UPLOADS_PATH}}.trash',
            'tmbURL'        => '{{UPLOADS_URL}}/.trash/.tmb/',
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
            'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
            'uploadAllow'   => array('image', 'text/plain'), // Same as above
            'uploadOrder'   => array('deny', 'allow'),      // Same as above
            'accessControl' => 'access',                    // Same as above
        ),
    )
);

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
