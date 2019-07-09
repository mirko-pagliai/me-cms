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
 * @see         MeCms\Controller\Admin\BackupsController::add()
 */
namespace MeCms\Form;

use Cake\Http\Exception\InternalErrorException;
use Cake\Validation\Validator;
use DatabaseBackup\Utility\BackupExport;
use MeCms\Form\Form;
use MeCms\Validation\AppValidator;

/**
 * BackupForm class
 */
class BackupForm extends Form
{
    /**
     * @var \DatabaseBackup\Utility\BackupExport
     */
    protected $BackupExport;

    /**
     * Defines the validator using the methods on Cake\Validation\Validator or
     *  loads a pre-defined validator from a concrete class.
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Validation\AppValidator
     */
    protected function _buildValidator(Validator $validator)
    {
        $validator = new AppValidator();

        //Filename
        $validator->add('filename', 'extension', [
            'message' => __d('me_cms', 'Valid extensions: {0}', 'sql, sql.gz, sql.bz2'),
            'rule' => ['custom', '/[^\.]\.sql(\.(bz2|gz))?$/'],
        ])->requirePresence('filename');

        return $validator;
    }

    /**
     * Internal method to get a `BackupExport` instance
     * @return \DatabaseBackup\Utility\BackupExport
     * @uses $BackupExport
     */
    protected function getBackupExportInstance()
    {
        $this->BackupExport = $this->BackupExport ?: new BackupExport();

        return $this->BackupExport;
    }

    /**
     * Used by `execute()` to execute the form's action
     * @param array $data Form data
     * @return string|null Filename or `null` on failure
     * @uses DatabaseBackup\Utility\BackupExport::filename()
     * @uses DatabaseBackup\Utility\BackupExport::export()
     * @uses getBackupExportInstance()
     */
    protected function _execute(array $data)
    {
        try {
            return $this->getBackupExportInstance()
                ->filename($data['filename'])
                ->export();
        } catch (InternalErrorException $e) {
            return null;
        }
    }
}
