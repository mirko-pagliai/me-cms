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
$isView = $this->getRequest()->is('action', 'view', 'Posts') && !$this->getRequest()->is('ajax');
$category = $post->get('category');
$user = $post->get('user');
?>

<article class="clearfix mb-5">
    <header class="mb-3 d-flex align-items-center">
        <?php
        if (getConfig('post.author_picture')) {
            echo $this->Thumb->fit($user->get('picture'), ['width' => 100], [
                'class' => 'flex-shrink-0 me-3 rounded-circle user-picture',
                'title' => __d('me_cms', 'Posted by {0}', $user->get('full_name')),
            ]);
        }
        ?>

        <div class="flex-grow-1">
            <?php if (getConfig('post.category') && $category) : ?>
                <h5 class="category fw-semibold lh-1 mb-1 text-uppercase">
                    <?= $this->Html->link($category->get('title'), $category->get('url'), ['class' => 'text-decoration-none']) ?>
                </h5>
            <?php endif; ?>

            <h2 class="title lh-sm">
                <?= $this->Html->link($post->get('title'), $post->get('url'), ['class' => 'text-decoration-none']) ?>
            </h2>

            <?php if ($post->hasValue('subtitle')) : ?>
                <h4 class="subtitle lh-1">
                    <?= $this->Html->link($post->get('subtitle'), $post->get('url'), ['class' => 'text-decoration-none']) ?>
                </h4>
            <?php endif; ?>

            <?php
            if (getConfig('post.author')) {
                echo $this->Html->div('author text-muted', __d('me_cms', 'Posted by {0}', $user->get('full_name')), ['icon' => 'user']);
            }

            if (getConfig('post.created')) {
                echo $this->Html->div('created text-muted', $this->Html->time(__d('me_cms', 'Posted on {0}', $post->get('created')->i18nFormat())));
            }

            if (getConfig('post.modified') && $post->get('modified') != $post->get('created')) {
                echo $this->Html->div('modified small text-muted', $this->Html->time(__d('me_cms', 'Updated on {0}', $post->get('modified')->i18nFormat())));
            }
            ?>
        </div>
    </header>

    <div class="body text-justify">
        <?php
        //Truncates the text when necessary. The text will be truncated to the location of the `<!-- readmore -->` tag.
        //  If the tag is not present, the value in the configuration will be used
        if (!$isView && !$this->getRequest()->is('action', 'preview')) {
            $strpos = strpos($post->get('text'), '<!-- read-more -->');
            if (!$strpos) {
                $strpos = getConfigOrFail('default.truncate_to');
                $truncatedOptions = ['html' => true];
            }
            $truncatedText = $this->Text->truncate($post->get('text'), $strpos, $truncatedOptions ?? ['ellipsis' => '']);
        }
        echo $truncatedText ?? $post->get('text');
        ?>
    </div>

    <?php if (getConfig('post.tags')) : ?>
        <ul class="tags list-inline mt-3">
        <?php foreach ($post->get('tags') as $tag) : ?>
            <li class="list-inline-item me-0 mb-2 small">
                <?= $this->Html->link($tag->get('tag'), $tag->get('url'), ['class' => 'p-1 text-decoration-none', 'icon' => 'tags']) ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php
    if (isset($truncatedText) && $truncatedText !== $post->get('text')) {
        echo $this->Html->button(__d('me_cms', 'Read more'), $post->get('url'), ['class' => 'fw-bold float-end mt-2 readmore small text-end']);
    }

    if (getConfig('post.shareaholic') && $isView) {
        echo $this->Html->div('mt-3', $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id')));
    }
    ?>

    <?php if (getConfig('disqus.shortname') && getConfig('post.enable_comments') && $post->get('enable_comments') && $isView) : ?>
    <div id="disqus_thread" class="mt-3"></div>
    <script>
    /*
    var disqus_config = function () {
    this.page.url = '<?= $post->get('url') ?>';
    this.page.identifier = '<?= sprintf('post-#%s', $post->get('id')) ?>';
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
