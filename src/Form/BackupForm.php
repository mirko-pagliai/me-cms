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
 */
namespace MeCms\Form;

use Cake\Form\Form;
use Cake\Network\Exception\InternalErrorException;
use MysqlBackup\Utility\BackupExport;

/**
 * BackupForm class.
 *
 * It is used by `MeCms\Controller\Admin\BackupsController::add()`.
 */
class BackupForm extends Form
{
    /**
     * Defines the validator using the methods on Cake\Validation\Validator or
     * loads a pre-defined validator from a concrete class.
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\AppValidator
     */
    protected function _buildValidator(\Cake\Validation\Validator $validator)
    {
        $validator = new \MeCms\Model\Validation\AppValidator();

        //Filename
        $validator->requirePresence('filename')
            ->remove('filename', 'validateUnique')
            ->add('filename', 'validExtension', [
                'rule' => function ($value, $context) {
                    $extensions = array_map(function ($v) {
                        return preg_quote($v, '/');
                    }, ['sql', 'sql.gz', 'sql.bz2']);

                    return (bool)preg_match(sprintf('/\.(%s)$/i', implode('|', $extensions)), $value);
                },
                'message' => __d('me_cms', 'Valid extensions: {0}', 'sql, sql.gz, sql.bz2'),
            ])
            ->add('filename', [
                'lengthBetween' => [
                    'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 100),
                    'rule' => ['lengthBetween', 3, 100],
                ],
            ]);

        return $validator;
    }

    /**
     * Used by `execute()` to execute the form's action
     * @param array $data Form data
     * @return bool
     * @uses MysqlBackup\Utility\BackupExport::filename()
     * @uses MysqlBackup\Utility\BackupExport::export()
     */
    protected function _execute(array $data)
    {
        try {
            $backup = new BackupExport();
            $backup->filename($data['filename']);

            return $backup->export();
        } catch (InternalErrorException $e) {
            return false;
        }
    }
}
