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
if (is_array($link)) {
    $link = $this->Url->build($link);
}
$linkOptions = optionsParser($linkOptions ?? [])->append('class', 'd-block');

if (!$linkOptions->exists('title')) {
    if (!empty($title)) {
        $linkOptions->add('title', $title);
    } elseif (!empty($text)) {
        $linkOptions->add('title', $text);
    }
}
?>

<a href="<?= $link ?>" <?= $linkOptions->toString() ?>>
    <div class="card border-0 text-white">
        <?= $this->Thumb->fit($path, ['width' => 275], ['class' => 'card-img rounded-0', 'alt' => $linkOptions->get('title')]) ?>
        <div class="card-img-overlay card-img-overlay-transition">
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
