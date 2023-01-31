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
 * @since       2.25.4
 */

namespace MeCms\TestSuite;

use Cake\Http\ServerRequest;
use MeCms\Model\Entity\User;
use MeCms\Model\Entity\UsersGroup;
use MeCms\Model\Table\AppTable;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * Abstract class for test controllers
 * @property \MeCms\Controller\AppController $_controller
 * @property \Cake\Http\Response $_response
 * @property \MeCms\Controller\AppController $Controller
 * @property \MeCms\Model\Table\AppTable $Table
 * @property array{controller: string, plugin: string} $url
 */
abstract class ControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Get magic method.
     *
     * It provides access to the cached properties of the test.
     * @param string $name Property name
     * @return mixed
     * @throws \ReflectionException
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'Controller':
                if (empty($this->_cache['Controller'])) {
                    $Request = new ServerRequest(['params' => $this->url]);
                    $this->_cache['Controller'] = new $this->originClassName($Request, null, $this->alias);
                }

                return $this->_cache['Controller'];
            case 'Table':
                if (empty($this->_cache['Table'])) {
                    if ($this->Controller->fetchTable() instanceof AppTable) {
                        $this->_cache['Table'] = $this->Controller->fetchTable();
                    }
                }

                return $this->_cache['Table'];
            case 'url':
                if (empty($this->_cache['url'])) {
                    $this->_cache['url'] = ['controller' => $this->alias, 'plugin' => $this->getPluginName($this)];
                }

                return $this->_cache['url'];
        }

        return parent::__get($name);
    }

    /**
     * Internal method to create an image to upload.
     *
     * Returns an array, similar to the `$_FILE` array that is created after an upload
     * @return array{tmp_name: string, error: int, name: string, type: string, size: int}
     */
    protected function createImageToUpload(): array
    {
        $file = TMP . 'file_to_upload.jpg';
        copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);

        return [
            'tmp_name' => $file,
            'error' => UPLOAD_ERR_OK,
            'name' => basename($file),
            'type' => mime_content_type($file) ?: '',
            'size' => filesize($file) ?: 0,
        ];
    }

    /**
     * Internal method to set the auth data (user group and user ID) for authentication and authorization
     * @param string $name Group name
     * @param int $id User ID
     * @return void
     */
    protected function setAuthData(string $name = 'user', int $id = 1): void
    {
        $this->session(['Auth' => new User(compact('id') + ['group' => new UsersGroup(compact('name'))])]);
    }
}
