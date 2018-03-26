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
$link = ['_name' => 'post', $post->slug];
$title = $post->title;
?>

<div class="card">
    <?php
    if (!isset($truncate['title']) || $truncate['title']) {
        $truncate['title'] = empty($truncate['title']) ? 40 : $truncate['title'];
        $title = $this->Text->truncate($title, $truncate['title'], ['exact' => false]);
    }
    echo $this->Html->link($title, $link, ['class' => 'card-header card-title p-2 text-truncate']);

    if (!empty($post->preview[0])) {
        $thumb = $this->Thumb->fit($post->preview[0]->url, ['width' => 205], ['class' => 'card-img rounded-0']);
        echo $this->Html->link($thumb, $link);
    }
    ?>

    <div class="card-body small p-2">
        <?php
        if ($post->has('text')) {
            $text = strip_tags($post->text);
            if (!isset($truncate['text']) || $truncate['text']) {
                $truncate['text'] = empty($truncate['text']) ? 80 : $truncate['text'];
                $text = $this->Text->truncate($text, $truncate['text'], ['exact' => false]);
            }
            echo $this->Html->para('card-text', $text);
        }
        ?>
    </div>
</div>