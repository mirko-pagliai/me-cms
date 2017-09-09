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

<article class="mb-4">
    <div class="header mb-3 pl-3">
        <?php if (getConfig('post.category') && $post->category->title && $post->category->slug) : ?>
            <h5 class="category mb-1">
                <?= $this->Html->link($post->category->title, ['_name' => 'postsCategory', $post->category->slug]) ?>
            </h5>
        <?php endif; ?>

        <h2 class="title mb-1">
            <?= $this->Html->link($post->title, ['_name' => 'post', $post->slug]) ?>
        </h2>

        <?php if ($post->subtitle) : ?>
            <h4 class="subtitle mb-1">
                <?= $this->Html->link($post->subtitle, ['_name' => 'post', $post->slug]) ?>
            </h4>
        <?php endif; ?>

        <div class="info mt-2 text-muted">
            <?php
            if (getConfig('post.author')) {
                echo $this->Html->div(
                    'author',
                    __d('me_cms', 'Posted by {0}', $post->user->full_name),
                    ['icon' => 'user']
                );
            }

            if (getConfig('post.created')) {
                echo $this->Html->time(
                    __d('me_cms', 'Posted on {0}', $post->created->i18nFormat()),
                    ['class' => 'date', 'icon' => 'clock-o']
                );
            }
            ?>
        </div>
    </div>

    <div class="content text-justify">
        <?php
        //Executes BBCode on the text
        $text = $this->BBCode->parser($post->text);

        //Truncates the text when necessary. The text will be truncated to the
        //  location of the `<!-- readmore -->` tag. If the tag is not present,
        //  the value in the configuration will be used
        if (!$this->request->isAction(['view', 'preview'])) {
            $strpos = strpos($text, '<!-- read-more -->');
            $strpos = $strpos ?: getConfigOrFail('default.truncate_to');

            $truncatedText = $this->Text->truncate($text, $strpos, ['html' => true]);
            echo $truncatedText;
        } else {
            echo $text;
        }
        ?>
    </div>

    <?php if (getConfig('post.tags') && $post->tags) : ?>
        <div class="tags mt-2">
            <?php foreach ($post->tags as $tag) : ?>
                <?= $this->Html->link($tag->tag, ['_name' => 'postsTag', $tag->slug], ['icon' => 'tags']) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="buttons mt-2 text-right">
        <?php
        //If it was requested to truncate the text and that has been
        //truncated, it shows the "Read more" link
        if (!empty($truncatedText) && $truncatedText !== $post->text) {
            echo $this->Html->button(
                __d('me_cms', 'Read more'),
                ['_name' => 'post', $post->slug],
                ['class' => ' readmore']
            );
        }
        ?>
    </div>

    <?php
    if (getConfig('post.shareaholic') && $this->request->isAction('view', 'Posts') && !$this->request->isAjax()) {
        echo $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id'));
    }
    ?>
</article>