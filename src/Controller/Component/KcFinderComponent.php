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
use Cake\Controller\ComponentRegistry;
use Cake\Filesystem\Folder;
use Cake\Routing\Router;
use MeCms\Utility\Checkup;
use Tools\Exception\NotWritableException;

/**
 * A component to handle KCFinder
 */
class KcFinderComponent extends Component
{
    /**
     * Instance of `Checkup`
     * @since 2.22.8
     * @var \MeCms\Utility\Checkup
     */
    public $Checkup;

    /**
     * Components
     * @var array
     */
    public $components = ['MeCms.Auth'];

    /**
     * Construct
     * @param ComponentRegistry $registry A ComponentRegistry this component can
     *  use to lazy load its components
     * @param array $config Array of configuration settings
     * @since 2.22.8
     * @uses $Checkup
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $this->Checkup = new Checkup;

        parent::__construct($registry, $config);
    }

    /**
     * Internal method to get the default config
     * @return array
     * @uses getTypes()
     */
    protected function getDefaultConfig()
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
     * Internal method to check if KCFinder is available
     * @return bool
     * @since 2.22.8
     * @uses $Checkup
     */
    protected function kcFinderIsAvailable()
    {
        return $this->Checkup->KCFinder->isAvailable();
    }

    /**
     * Internal method to check if the uploaded directory is writeable
     * @return bool
     * @since 2.22.8
     * @uses $Checkup
     */
    protected function uploadedDirIsWriteable()
    {
        $result = $this->Checkup->Webroot->isWriteable();

        return $result && array_key_exists(UPLOADED, $result) ? $result[UPLOADED] : false;
    }

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this
     *  component
     * @return void
     * @throws ErrorException
     * @throws NotWritableException
     * @uses getDefaultConfig()
     * @uses kcFinderIsAvailable()
     * @uses uploadedDirIsWriteable()
     */
    public function initialize(array $config)
    {
        //Checks for KCFinder and for the files directory (`APP/webroot/files`)
        is_true_or_fail($this->kcFinderIsAvailable(), __d('me_tools', '{0} is not available', 'KCFinder'));
        is_true_or_fail($this->uploadedDirIsWriteable(), __d('me_tools', 'File or directory `{0}` is not writable', rtr(UPLOADED)), NotWritableException::class);

        //Merges and writes on session:
        //  1) default config;
        //  2) options from configuration;
        //  3) passed options.
        $config = array_merge($this->getDefaultConfig(), getConfig('kcfinder', []), $config);
        $this->getController()->request->getSession()->write('KCFINDER', $config);

        parent::initialize($config);
    }
}
