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
 * @var \MeCms\Model\Entity\Post $post
 * @var \MeCms\View\View\AppView $this
 */
?>

<div class="card mb-4">
    <?php if ($post->hasValue('preview')): ?>
    <?= $this->Thumb->fit($post->get('preview')[0]->get('url'), ['width' => 205],  ['class' => 'card-img-top', 'url' => $post->get('url')]) ?>
    <?php endif; ?>

    <div class="card-body">
        <h5 class="card-title text-truncate">
            <?= $this->Html->link($post->get('title'), $post->get('url'), ['class' => 'card-title text-decoration-none text-truncate']) ?>
        </h5>
        <p class="card-text small">
            <?php
            if (!isset($truncate['text']) || $truncate['text']) {
                echo $this->Text->truncate($post->get('plain_text'), $truncate['text'] ?? 80, ['exact' => false]);
            } else {
                echo $post->get('plain_text');
            }
            ?>
        </p>
    </div>
</div>
