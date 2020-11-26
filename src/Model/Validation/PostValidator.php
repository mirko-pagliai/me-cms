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

namespace MeCms\Model\Validation;

use Cake\Utility\Hash;
use MeCms\Model\Validation\TagValidator;
use MeCms\Validation\PageAndPostValidator;

/**
 * Post validator class
 */
class PostValidator extends PageAndPostValidator
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->requirePresence('user_id', 'create');

        $this->add('tags', [
            'validTags' => [
                'last' => true,
                'rule' => [$this, 'validTags'],
            ],
        ])->allowEmptyString('tags');
    }

    /**
     * Tags validation method.
     *
     * It uses the `TagValidator`, checks its rules on each tag and returns
     *  `true` on success or a string with all errors found on failure.
     * @param array $values Field values
     * @return bool|string `true` on success or an error message on failure
     * @since 2.26.1
     * @uses \MeCms\Model\Validation\TagValidator
     */
    public function validTags(array $values)
    {
        $validator = new TagValidator();
        $messages = [];

        foreach ($values as $tag) {
            $errors = Hash::get($validator->validate($tag), 'tag') ?: [];

            foreach ($errors as $error) {
                $messages[] = __d('me_cms', 'Tag "{0}": {1}', $tag['tag'], lcfirst($error));
            }
        }

        return !$messages ?: implode(PHP_EOL, $messages);
    }
}
