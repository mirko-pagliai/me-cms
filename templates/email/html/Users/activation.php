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
 * @var \MeCms\View\View\AppView $this
 * @var string $url
 */
?>

<?= __d('me_cms', 'Hello {0}', $fullName) ?>,

<?= __d('me_cms', 'you have signed on the site {0}', getConfigOrFail('main.title')) ?>.

<?= __d('me_cms', 'To activate your account, click {0}', $this->Html->link(__d('me_cms', 'here'), $url)) ?>.

<?= __d('me_cms', 'If you have not made this request, please contact an administrator') ?>.
