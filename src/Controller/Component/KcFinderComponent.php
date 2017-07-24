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
 * @see         http://kcfinder.sunhater.com/install
 */
namespace MeCms\Controller\Component;

use Cake\Controller\Component;
use Cake\Filesystem\Folder;
use Cake\Network\Exception\InternalErrorException;
use Cake\Routing\Router;

/**
 * A component to handle KCFinder
 */
class KcFinderComponent extends Component
{
    /**
     * Components
     * @var array
     */
    public $components = [ME_CMS . '.Auth'];

    /**
     * Gets the default config
     * @return array
     * @uses getTypes()
     */
    protected function _getDefaultConfig()
    {
        $defaultConfig = [
            'denyExtensionRename' => true,
            'denyUpdateCheck' => true,
            'dirnameChangeChars' => [' ' => '_', ':' => '_'],
            'disabled' => false,
            'filenameChangeChars' => [' ' => '_', ':' => '_'],
            'jpegQuality' => 100,
            'uploadDir' => UPLOADED,
            'uploadURL' => Router::url('/files', true),
            'types' => $this->getTypes(),
        ];

        //If the user is not and admin
        if (!$this->Auth->isGroup(['admin'])) {
            //Only admins can delete or rename directories
            $defaultConfig['access']['dirs'] = [
                'create' => true,
                'delete' => false,
                'rename' => false,
            ];
            //Only admins can delete, move or rename files
            $defaultConfig['access']['files'] = [
                'upload' => true,
                'delete' => false,
                'copy' => true,
                'move' => false,
                'rename' => false,
            ];
        }

        return $defaultConfig;
    }

    /**
     * Gets the file types supported by KCFinder
     * @return array
     */
    public function getTypes()
    {
        //Gets the folders list
        list($folders) = (new Folder(UPLOADED))->read(true, true);

        //Each folder is a file type supported by KCFinder
        foreach ($folders as $type) {
            $types[$type] = '';
        }

        //Adds the "images" type by default
        $types['images'] = '*img';

        return $types;
    }

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this
     *  component
     * @return void
     * @throws InternalErrorException
     * @uses _getDefaultConfig()
     */
    public function initialize(array $config)
    {
        //Checks for KCFinder
        if (!is_readable(WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . 'index.php')) {
            throw new InternalErrorException(__d('me_tools', '{0} is not available', 'KCFinder'));
        }

        //Checks for the files directory (`APP/webroot/files`)
        if (!folderIsWriteable(UPLOADED)) {
            throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr(UPLOADED)));
        }

        //Merges:
        //  1) default config;
        //  2) options from configuration;
        //  3) passed options.
        $config = array_merge(
            $this->_getDefaultConfig(),
            getConfig('kcfinder', []),
            $config
        );

        //Writes on session
        $this->getController()->request->session()->write('KCFINDER', $config);

        parent::initialize($config);
    }
}
