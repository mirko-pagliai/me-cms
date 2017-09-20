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
if (is_array($link)) {
    $link = $this->Url->build($link);
}

if (empty($linkOptions)) {
    $linkOptions = [];
}

if (empty($linkOptions['title'])) {
    if (!empty($title)) {
        $linkOptions['title'] = $title;
    } elseif (!empty($text)) {
        $linkOptions['title'] = $text;
    }
}
?>

<a href="<?= $link ?>" <?= toAttributes($linkOptions) ?>>
    <div class="card border-0 text-white">
        <?= $this->Thumb->fit($path, ['width' => 275], ['class' => 'card-img rounded-0']) ?>
        <div class="card-img-overlay card-img-overlay-transition p-3">
            <?php if (!empty($title)) : ?>
            <h5 class="card-title"><?= strip_tags($title) ?></h5>
            <?php endif; ?>

            <?php if (!empty($text)) : ?>
            <p class="card-text small">
                <?= $this->Text->truncate(strip_tags($text), 150) ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</a>