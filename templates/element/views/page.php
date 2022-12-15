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
 * @var \MeCms\Model\Entity\Page $page
 * @var \MeCms\View\View\AppView $this
 */
$isView = $this->getRequest()->is('action', 'view') && !$this->getRequest()->is('ajax');
$category = $page->get('category');
?>

<article class="clearfix mb-5">
    <header class="mb-3">
        <?php if (getConfig('page.category') && $category) : ?>
            <h5 class="category fw-semibold lh-1 mb-1 text-uppercase">
                <?= $this->Html->link($category->get('title'), $category->get('url'), ['class' => 'text-decoration-none']) ?>
            </h5>
        <?php endif; ?>

        <h2 class="title lh-sm">
            <?= $this->Html->link($page->get('title'), $page->get('url'), ['class' => 'text-decoration-none']) ?>
        </h2>

        <?php if ($page->hasValue('subtitle')) : ?>
            <h4 class="subtitle lh-1">
                <?= $this->Html->link($page->get('subtitle'), $page->get('url'), ['class' => 'text-decoration-none']) ?>
            </h4>
        <?php endif; ?>

        <?php
        if (getConfig('page.created')) {
            echo $this->Html->div('created text-muted', $this->Html->time(__d('me_cms', 'Posted on {0}', $page->get('created')->i18nFormat())));
        }

        if (getConfig('page.modified') && $page->get('modified') != $page->get('created')) {
            echo $this->Html->div('modified small text-muted', $this->Html->time(__d('me_cms', 'Updated on {0}', $page->get('modified')->i18nFormat())));
        }
        ?>
    </header>

    <div class="body text-justify">
        <?php
        //Truncates the text when necessary. The text will be truncated to the location of the `<!-- readmore -->` tag.
        //  If the tag is not present, the value in the configuration will be used
        if (!$isView && !$this->getRequest()->is('action', 'preview')) {
            $strpos = strpos($page->get('text'), '<!-- read-more -->');
            if (!$strpos) {
                $strpos = getConfigOrFail('default.truncate_to');
                $truncatedOptions = ['html' => true];
            }
            $truncatedText = $this->Text->truncate($page->get('text'), $strpos, $truncatedOptions ?? ['ellipsis' => '']);
        }
        echo $truncatedText ?? $page->get('text');
        ?>
    </div>

    <?php
    if (isset($truncatedText) && $truncatedText !== $page->get('text')) {
        echo $this->Html->button(__d('me_cms', 'Read more'), $page->get('url'), ['class' => 'fw-bold float-end mt-2 readmore small text-end']);
    }

    if (getConfig('page.shareaholic') && $isView) {
        echo $this->Html->div('mt-3', $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id')));
    }
    ?>

    <?php if (getConfig('disqus.shortname') && getConfig('page.enable_comments') && $page->get('enable_comments') && $isView) : ?>
    <div id="disqus_thread" class="mt-3"></div>
    <script>
    /*
    var disqus_config = function () {
    this.page.url = '<?= $page->get('url') ?>';
    this.page.identifier = '<?= sprintf('page-#%s', $page->get('id')) ?>';
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
