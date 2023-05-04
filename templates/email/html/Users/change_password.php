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
 *
 * @var string $fullName
 */
?>

<?= __d('me_cms', 'Hello {0}', $fullName) ?>,

<?= __d('me_cms', 'you have recently changed your password on our site {0}', getConfigOrFail('main.title')) ?>.

<?= __d('me_cms', 'If you have not made this request, please contact an administrator') ?>.
