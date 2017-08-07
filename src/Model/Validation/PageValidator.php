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
 * Page validator class
 */
class PageValidator extends AppValidator
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

        //Category
        $this->add('category_id', [
            'naturalNumber' => [
                'message' => I18N_SELECT_VALID_OPTION,
                'rule' => 'naturalNumber',
            ],
        ])->requirePresence('category_id', 'create');

        //Title
        $this->requirePresence('title', 'create');

        //Slug
        $this->requirePresence('slug', 'create');

        //Text
        $this->requirePresence('text', 'create');
    }
}
