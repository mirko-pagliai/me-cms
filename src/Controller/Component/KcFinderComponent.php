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
 * @see         http://kcfinder.sunhater.com/install
 */

namespace MeCms\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use MeCms\Utility\Checkup;
use Symfony\Component\Finder\Finder;
use Tools\Exception\NotWritableException;

/**
 * A component to handle KCFinder
 */
class KcFinderComponent extends Component
{
    /**
     * Default config
     * @var array
     */
    protected $_defaultConfig = [
        'access' => [
            'dirs' => [
                'create' => true,
                'delete' => false,
                'rename' => false,
            ],
            'files' => [
                'upload' => true,
                'delete' => false,
                'copy' => true,
                'move' => false,
                'rename' => false,
            ],
        ],
        'denyExtensionRename' => true,
        'denyUpdateCheck' => true,
        'dirnameChangeChars' => [' ' => '_', ':' => '_'],
        'disabled' => false,
        'filenameChangeChars' => [' ' => '_', ':' => '_'],
        'jpegQuality' => 100,
        'types' => [],
        'uploadDir' => UPLOADED,
        'uploadURL' => null,
    ];

    /**
     * Components
     * @var array
     */
    public $components = ['MeCms.Auth'];

    /**
     * Gets the file types supported by KCFinder
     * @return array
     */
    public function getTypes(): array
    {
        $folders = objects_map(iterator_to_array((new Finder())->directories()->in(UPLOADED)), 'getFilename');

        //Each folder is a file type supported by KCFinder
        //Adds the default "images" type and returns
        return array_fill_keys($folders, '') + ['images' => '*img'];
    }

    /**
     * Internal method to check if KCFinder is available
     * @return bool
     * @since 2.22.8
     */
    protected function kcFinderIsAvailable(): bool
    {
        return (new Checkup())->KCFinder->isAvailable();
    }

    /**
     * Internal method to check if the uploaded directory is writeable
     * @return bool
     * @since 2.22.8
     */
    protected function uploadedDirIsWriteable(): bool
    {
        $result = (new Checkup())->Webroot->isWriteable();

        return $result && array_key_exists(UPLOADED, $result) ? $result[UPLOADED] : false;
    }

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this
     *  component
     * @return void
     * @throws \ErrorException
     * @throws \Tools\Exception\NotWritableException
     * @uses kcFinderIsAvailable()
     * @uses uploadedDirIsWriteable()
     */
    public function initialize(array $config): void
    {
        //Checks for KCFinder and for the files directory (`APP/webroot/files`)
        is_true_or_fail($this->kcFinderIsAvailable(), __d('me_tools', '{0} is not available', 'KCFinder'));
        is_true_or_fail($this->uploadedDirIsWriteable(), __d('me_tools', 'File or directory `{0}` is not writable', rtr(UPLOADED)), NotWritableException::class);

        //Admins can do any action
        if ($this->Auth->isGroup(['admin'])) {
            $config['access'] = [];
        }

        $config += [
            'uploadURL' => Router::url('/files', true),
            'types' => $this->getTypes(),
        ];

        //Sets config and writes it on session
        $this->configShallow($config + getConfig('kcfinder', []));
        $this->getController()->getRequest()->getSession()->write('KCFINDER', $this->getConfig());
    }
}
