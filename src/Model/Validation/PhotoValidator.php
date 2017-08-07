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
 */
namespace MeCms\Model\Validation;

use MeCms\Model\Validation\AppValidator;

/**
 * Photo validator class
 */
class PhotoValidator extends AppValidator
{
    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses MeCms\Model\Validation\AppValidator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //Album
        $this->add('album_id', [
            'naturalNumber' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'naturalNumber',
            ],
        ])->requirePresence('album_id', 'create');

        //Filename
        $this->add('filename', [
            'extension' => [
                'message' => __d('me_cms', 'Valid extensions: {0}', 'gif, jpg, jpeg, png'),
                'rule' => ['extension', ['gif', 'jpg', 'jpeg', 'png']],
            ],
        ])->requirePresence('filename', 'create');
    }
}
