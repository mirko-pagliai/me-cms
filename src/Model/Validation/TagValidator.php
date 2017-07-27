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
use MeCms\Model\Validation\Traits\TagValidatorTrait;

/**
 * Tag validator class
 */
class TagValidator extends AppValidator
{
    use TagValidatorTrait;

    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses MeCms\Model\Validation\AppValidator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //Tag
        $this->add('tag', [
            'validTagLength' => [
                'last' => true,
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 30),
                'rule' => [$this, 'validTagLength'],
            ],
            'validTagChars' => [
                'message' => sprintf(
                    '%s: %s',
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'lowercase letters, numbers, space')
                ),
                'rule' => [$this, 'validTagChars'],
            ],
        ]);
    }
}
