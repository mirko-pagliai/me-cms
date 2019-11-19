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
 * @since       2.26.6
 */

namespace MeCms\Validation;

use MeCms\Validation\AppValidator;

/**
 * Abstract class for `BannerValidator` and `PhotoValidator` classes.
 *
 * This class provides some methods and properties common to both classes.
 */
abstract class BannerAndPhotoValidator extends AppValidator
{
    /**
     * Valid extensions
     */
    protected const VALID_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png'];

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->add('filename', [
            'extension' => [
                'message' => __d('me_cms', 'Valid extensions: {0}', implode(', ', self::VALID_EXTENSIONS)),
                'rule' => ['extension', self::VALID_EXTENSIONS],
            ],
        ])->requirePresence('filename', 'create');
    }
}
