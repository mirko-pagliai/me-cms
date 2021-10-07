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
 */

namespace MeCms\Form;

use Cake\Form\Form;
use Cake\Validation\Validator;
use DatabaseBackup\Utility\BackupExport;
use Exception;
use MeCms\Validation\AppValidator;

/**
 * BackupForm class
 * @see \DatabaseBackup\Utility\BackupExport
 * @see \MeCms\Controller\Admin\BackupsController::add()
 */
class BackupForm extends Form
{
    /**
     * @var \DatabaseBackup\Utility\BackupExport
     */
    protected $BackupExport;

    /**
     * Returns the default validator object
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = new AppValidator();

        $validator->add('filename', [
            'maxLength' => [
                'message' => __d('me_cms', 'Must be at most {0} chars', 255),
                'rule' => ['maxLength', 255],
            ],
            'extension' => [
                'message' => __d('me_cms', 'Valid extensions: {0}', 'sql, sql.gz, sql.bz2'),
                'rule' => ['custom', '/[^\.]\.sql(\.(bz2|gz))?$/'],
            ],
        ])->requirePresence('filename');

        return $validator;
    }

    /**
     * Internal method to get a `BackupExport` instance
     * @return \DatabaseBackup\Utility\BackupExport
     */
    protected function getBackupExportInstance(): BackupExport
    {
        $this->BackupExport = $this->BackupExport ?: new BackupExport();

        return $this->BackupExport;
    }

    /**
     * Used by `execute()` to execute the form's action
     * @param array $data Form data
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        try {
            return (bool)$this->getBackupExportInstance()
                ->filename($data['filename'])
                ->export();
        } catch (Exception $e) {
            return false;
        }
    }
}
