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

<div class="card">
    <?php
    $title = $post->get('title');
    if (!isset($truncate['title']) || $truncate['title']) {
        $truncate['title'] = isset($truncate['title']) ? $truncate['title'] : 40;
        $title = $this->Text->truncate($title, $truncate['title'], ['exact' => false]);
    }
    echo $this->Html->link($title, $post->get('url'), ['class' => 'card-header card-title p-2 text-truncate']);

    if ($post->has('preview')) {
        echo $this->Thumb->fit(
            $post->get('preview')[0]->get('url'),
            ['width' => 205],
            ['class' => 'card-img rounded-0', 'url' => $post->get('url')]
        );
    }

    $text = $post->get('plain_text');
    if (!isset($truncate['text']) || $truncate['text']) {
        $truncate['text'] = isset($truncate['text']) ? $truncate['text'] : 80;
        $text = $this->Text->truncate($text, $truncate['text'], ['exact' => false]);
    }
    echo $this->Html->div('card-body small p-2', $this->Html->para('card-text', $text));
    ?>
</div>
