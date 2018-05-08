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

use MeCms\Model\Validation\Traits\TagValidatorTrait;
use MeCms\Validation\AppValidator;

/**
 * Post validator class
 */
class PostValidator extends AppValidator
{
    use TagValidatorTrait;

    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses MeCms\Validation\AppValidator::__construct()
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

        //User (author)
        $this->requirePresence('user_id', 'create');

        //Title
        $this->requirePresence('title', 'create');

        //Slug
        $this->requirePresence('slug', 'create');

        //Text
        $this->requirePresence('text', 'create');

        //Tags
        $this->add('tags', [
            'validTagsLength' => [
                'last' => true,
                'message' => __d('me_cms', 'Each tag must be between {0} and {1} chars', 3, 30),
                'rule' => [$this, 'validTagsLength'],
            ],
            'validTagsChars' => [
                'message' => sprintf(
                    '%s: %s',
                    I18N_ALLOWED_CHARS,
                    __d('me_cms', 'lowercase letters, numbers, space')
                ),
                'rule' => [$this, 'validTagsChars'],
            ],
        ])->allowEmpty('tags');
    }

    /**
     * Tags validation method (length).
     * For each tag, it checks if the tag has a valid length
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     * @use \MeCms\Model\Validation\Traits\TagValidatorTrait::validTagLength()
     */
    public function validTagsLength($value, $context)
    {
        foreach ($value as $tag) {
            //Checks if each tag has has a valid length
            if (empty($tag['tag']) || !$this->validTagLength($tag['tag'], $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tags validation method (syntax).
     * For each tag, it checks if the tag has a valid syntax
     * @param string $value Field value
     * @param array $context Field context
     * @return bool
     * @use \MeCms\Model\Validation\Traits\TagValidatorTrait::validTagChars()
     */
    public function validTagsChars($value, $context)
    {
        foreach ($value as $tag) {
            //Checks if the tag has a valid syntax
            if (empty($tag['tag']) || !$this->validTagChars($tag['tag'], $context)) {
                return false;
            }
        }

        return true;
    }
}
