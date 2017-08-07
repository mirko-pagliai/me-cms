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

<div class="clearfix">
    <?php foreach ($albums as $album) : ?>
        <div class="col-sm-6 col-md-4">
            <div class="photo-box">
                <a href="<?= $this->Url->build(['_name' => 'album', $album->slug]) ?>" class="thumbnail" title="<?= $album->title ?>">
                    <?= $this->Thumb->fit(collection($album->photos)->extract('path')->first(), ['width' => 275]) ?>
                    <div class="photo-info">
                        <div>
                            <p><strong><?= $album->title ?></strong></p>
                            <p><small><?= __d('me_cms', '{0} photos', $album->photo_count) ?></small></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>