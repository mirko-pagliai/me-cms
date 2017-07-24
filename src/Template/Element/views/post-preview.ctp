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

<div class="content-preview">
    <a href="<?= $this->Url->build(['_name' => 'post', $post->slug]) ?>">
        <div>
            <div>
                <div class="content-title">
                    <?php
                    if (isset($truncate['title']) && !$truncate['title']) {
                        echo $post->title;
                    } else {
                        echo $this->Text->truncate(
                            $post->title,
                            empty($truncate['title']) ? 40 : $truncate['title'],
                            ['exact' => false]
                        );
                    }
                    ?>
                </div>
                <?php if ($post->text) : ?>
                    <div class="content-text">
                        <?php
                        if (isset($truncate['text']) && !$truncate['text']) {
                            echo strip_tags($post->text);
                        } else {
                            echo $this->Text->truncate(
                                strip_tags($post->text),
                                empty($truncate['text']) ? 80 : $truncate['text'],
                                ['exact' => false]
                            );
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        if ($post->preview) {
            echo $this->Thumb->fit($post->preview['preview'], ['width' => 205]);
        }
        ?>
    </a>
</div>