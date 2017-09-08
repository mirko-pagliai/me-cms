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
$this->extend('/Common/index');
$this->assign('title', $title = I18N_PHOTOS);

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title, ['_name' => 'albums']);
?>

<div class="row">
    <?php foreach ($albums as $album) : ?>
    <div class="col-4 mb-4">
        <a href="<?= $this->Url->build(['_name' => 'album', $album->slug]) ?>" class="d-block" title="<?= $album->title ?>">
            <div class="card border-0 text-white">
                <?= $this->Thumb->fit($album->preview, ['width' => 275], ['class' => 'card-img rounded-0']) ?>
                <div class="card-img-overlay card-img-overlay-transition">
                    <h4 class="card-title"><?= $album->title ?></h4>
                    <p class="card-text">
                        <?= __d('me_cms', '{0} photos', $album->photo_count) ?>
                    </p>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>