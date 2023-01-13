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
use MeCms\Controller\AppController;
use MeCms\Model\Entity\User;
use MeCms\Model\Entity\UsersGroup;
use MeCms\Model\Table\AppTable;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * Abstract class for test controllers
 * @property \MeCms\Controller\AppController $_controller
 * @property \Cake\Http\Response $_response
 */
abstract class ControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var \MeCms\Controller\AppController|(\MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject)
     */
    protected AppController $Controller;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected bool $autoInitializeClass = true;

    /**
     * @var array{controller: string, plugin: string, prefix: ?string}
     */
    protected array $url;

    /**
     * Called before every test method
     * @return void
     * @throws \ReflectionException
     * @noinspection PhpRedundantVariableDocTypeInspection
     */
    protected function setUp(): void
    {
        parent::setUp();

        //Tries to retrieve controller and table from the class name
        if (empty($this->Controller) && $this->autoInitializeClass) {
            /** @var class-string<\MeCms\Controller\AppController> $originClassName */
            $originClassName = $this->getOriginClassNameOrFail($this);
            $alias = $this->getAlias($originClassName);

            $this->url = ['controller' => $alias, 'prefix' => null, 'plugin' => $this->getPluginName($this)];
            $Request = new ServerRequest(['params' => $this->url]);

            if (!(new \ReflectionClass($originClassName))->isAbstract()) {
                $Controller = new $originClassName($Request, null, $alias);

                if (empty($this->Table) && $Controller->fetchTable() instanceof AppTable) {
                    $this->Table = $Controller->fetchTable();
                }
            }
            $this->Controller = $Controller ?? $this->getMockForAbstractClass($originClassName, [$Request, null, $alias]);
        }
    }

    /**
     * Internal method to create an image to upload.
     *
     * Returns an array, similar to the `$_FILE` array that is created after an upload
     * @return array{tmp_name: string, error: 0, name: string, type: string, size: int}
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
