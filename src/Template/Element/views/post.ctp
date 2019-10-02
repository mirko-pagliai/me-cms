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
$isView = $this->getRequest()->isAction('view', 'Posts') && !$this->getRequest()->isAjax();
?>

<article class="clearfix mb-4">
    <header class="mb-3 media">
        <?php
        if (getConfig('post.author_picture') && $post->user->has('picture')) {
            echo $this->Thumb->fit($post->user->picture, ['width' => 100], [
                'class' => 'd-none d-sm-block mr-3 rounded-circle user-picture',
                'title' => __d('me_cms', 'Posted by {0}', $post->user->full_name),
            ]);
        }
        ?>

        <div class="media-body">
            <?php if (getConfig('post.category') && $post->category->has(['slug', 'title'])) : ?>
                <h5 class="category mb-2">
                    <?= $this->Html->link($post->category->title, ['_name' => 'postsCategory', $post->category->slug]) ?>
                </h5>
            <?php endif; ?>

            <h2 class="title mb-2">
                <?= $this->Html->link($post->title, ['_name' => 'post', $post->slug]) ?>
            </h2>

            <?php if ($post->has('subtitle')) : ?>
                <h4 class="subtitle mb-2">
                    <?= $this->Html->link($post->subtitle, ['_name' => 'post', $post->slug]) ?>
                </h4>
            <?php endif; ?>

            <div class="info">
                <?php
                if (getConfig('post.author') && $post->user->has('full_name')) {
                    echo $this->Html->div('author', __d('me_cms', 'Posted by {0}', $post->user->full_name), ['icon' => 'user']);
                }

                $created = $post->get('created');
                if (getConfig('post.created') && $created) {
                    echo $this->Html->div('created', $this->Html->time(
                        __d('me_cms', 'Posted on {0}', $created->i18nFormat()),
                        ['class' => 'date', 'icon' => 'clock']
                    ));
                }

                $modified = $post->get('modified');
                if (getConfig('post.modified') && $modified && $modified != $created) {
                    echo $this->Html->div('modified small', $this->Html->time(
                        __d('me_cms', 'Updated on {0}', $modified->i18nFormat()),
                        ['class' => 'date', 'icon' => 'clock']
                    ));
                }
                ?>
            </div>
        </div>
    </header>

    <main class="text-justify">
        <?php
        $text = $post->get('plain_text');

        //Truncates the text when necessary. The text will be truncated to the
        //  location of the `<!-- readmore -->` tag. If the tag is not present,
        //  the value in the configuration will be used
        if (!$this->getRequest()->isAction(['view', 'preview'])) {
            $strpos = strpos($text, '<!-- read-more -->');
            $truncatedOptions = ['ellipsis' => false];

            if (!$strpos) {
                $strpos = getConfigOrFail('default.truncate_to');
                $truncatedOptions = ['html' => true];
            }

            $text = $this->Text->truncate($text, $strpos, $truncatedOptions);
        }
        echo $text;
        ?>
    </main>

    <?php if (getConfig('post.tags') && $post->has('tags')) : ?>
        <div class="tags mt-2">
            <?php foreach ($post->tags as $tag) : ?>
                <?= $this->Html->link($tag->tag, ['_name' => 'postsTag', $tag->slug], ['icon' => 'tags']) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($truncatedText) && $truncatedText !== $post->text) : ?>
    <div class="buttons mt-2 text-right">
        <?= $this->Html->button(__d('me_cms', 'Read more'), ['_name' => 'post', $post->slug], ['class' => ' readmore']) ?>
    </div>
    <?php endif; ?>

    <?php if (getConfig('post.shareaholic') && $isView) : ?>
    <div class="mt-3">
        <?= $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id')) ?>
    </div>
    <?php endif; ?>

    <?php if (getConfig('disqus.shortname') && getConfig('post.enable_comments') && $post->get('enable_comments') && $isView) : ?>
    <div id="disqus_thread" class="mt-3"></div>
    <script>
    /*
    var disqus_config = function () {
    this.page.url = '<?= $this->Url->build(['_name' => 'post', $post->slug], true) ?>';
    this.page.identifier = '<?= sprintf('post-#%s', $post->id) ?>';
    };
    */
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://<?= getConfig('disqus.shortname') ?>.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
    </script>
    <?php endif; ?>
</article>
