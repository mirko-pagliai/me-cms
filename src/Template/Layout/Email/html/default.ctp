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
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?= $this->Html->title(getConfigOrFail('main.title')) ?>

    </head>
    <body>
        <?php
        foreach (explode("\n", trim($this->fetch('content'))) as $row) {
            echo $this->Html->para(null, $row);
        }
        ?>

        <small>
            <?= __d('me_cms', 'This email was sent automatically from {0}', $this->Html->link(
                getConfigOrFail('main.title'),
                $this->Url->build('/', true)
            )) ?>
        </small>
        <br />

        <small>
            <?= __d('me_cms', 'The request has been sent from the IP {0}', $this->getRequest()->clientIp()) ?>
        </small>
        <br />

        <small>
            <?= __d('me_cms', 'Please, don\'t reply to this email') ?>
        </small>
    </body>
</html>
