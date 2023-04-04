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
 * @var \MeCms\View\View\AppView $this
 */

$this->extend('/common/view');
?>

<div class="text-center">
    <p><?= __d('me_cms', 'Your IP address is not allowed') ?></p>

    <p>
        <?= __d(
            'me_cms',
            'You can send us an email to {0}',
            $this->Mailhide->link(getConfigOrFail('email.webmaster'), getConfigOrFail('email.webmaster'))
        ) ?>
    </p>
</div>
