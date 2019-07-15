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
 * @since       2.26.6
 */
namespace MeCms\Validation;

use MeCms\Validation\AppValidator;

/**
 * Abstract class for `PageValidator` and `PostValidator` classes.
 *
 * This class provides some methods and properties common to both classes.
 */
abstract class PageAndPostValidator extends AppValidator
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->add('category_id', [
            'naturalNumber' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'naturalNumber',
            ],
        ])->requirePresence('category_id', 'create');

        $this->requirePresence('title', 'create');
        $this->requirePresence('slug', 'create');
        $this->requirePresence('text', 'create');
    }
}
