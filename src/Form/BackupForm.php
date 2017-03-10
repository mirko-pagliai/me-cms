<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 * @see         MeCms\Controller\Admin\BackupsController::add()
 */
namespace MeCms\Form;

use Cake\Form\Form;
use Cake\Network\Exception\InternalErrorException;
use MeCms\Model\Validation\AppValidator;
use MysqlBackup\Utility\BackupExport;

/**
 * BackupForm class
 */
class BackupForm extends Form
{
    /**
     * @var \MysqlBackup\Utility\BackupExport
     */
    protected $_BackupExport;

    /**
     * Defines the validator using the methods on Cake\Validation\Validator or
     * loads a pre-defined validator from a concrete class.
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\AppValidator
     */
    protected function _buildValidator(\Cake\Validation\Validator $validator)
    {
        $validator = new AppValidator;

        //Filename
        $validator->add('filename', 'extension', [
            'message' => __d('me_cms', 'Valid extensions: {0}', 'sql, sql.gz, sql.bz2'),
            'rule' => function ($value) {
                return (bool)preg_match('/[^\.]\.sql(\.(bz2|gz))?$/', $value);
            },
        ])->requirePresence('filename');

        return $validator;
    }

    /**
     * Gets a `BackupExport` instance
     * @return \MysqlBackup\Utility\BackupExport
     * @uses $_BackupExport
     */
    protected function _getBackupExportInstance()
    {
        if (empty($this->_BackupExport)) {
            $this->_BackupExport = new BackupExport;
        }

        return $this->_BackupExport;
    }

    /**
     * Used by `execute()` to execute the form's action
     * @param array $data Form data
     * @return string|bool Filename or `false` on failure
     * @uses MysqlBackup\Utility\BackupExport::filename()
     * @uses MysqlBackup\Utility\BackupExport::export()
     * @uses _getBackupExportInstance()
     */
    protected function _execute(array $data)
    {
        try {
            return $this->_getBackupExportInstance()
                ->filename($data['filename'])
                ->export();
        } catch (InternalErrorException $e) {
            return false;
        }
    }
}
