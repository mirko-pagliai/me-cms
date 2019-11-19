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
$isView = $this->getRequest()->isAction('view', 'Pages') && !$this->getRequest()->isAjax();
$category = $page->get('category');
?>

<article class="clearfix mb-4">
    <header class="mb-3">
        <?php if (getConfig('page.category')) : ?>
            <h5 class="category mb-2">
                <?= $this->Html->link($category->get('title'), $category->get('url')) ?>
            </h5>
        <?php endif; ?>

        <h3 class="title mb-2">
            <?= $this->Html->link($page->get('title'), $page->get('url')) ?>
        </h3>

        <?php if ($page->has('subtitle')) : ?>
            <h4 class="subtitle mb-2">
                <?= $this->Html->link($page->get('subtitle'), $page->get('url')) ?>
            </h4>
        <?php endif; ?>

        <div class="info">
            <?php
            $created = $page->get('created');
            if (getConfig('page.created')) {
                echo $this->Html->time(
                    __d('me_cms', 'Posted on {0}', $created->i18nFormat()),
                    ['class' => 'date', 'icon' => 'clock']
                );
            }

            $modified = $page->get('modified');
            if (getConfig('page.modified') && $modified != $created) {
                echo $this->Html->div('modified small', $this->Html->time(
                    __d('me_cms', 'Updated on {0}', $modified->i18nFormat()),
                    ['class' => 'date', 'icon' => 'clock']
                ));
            }
            ?>
        </div>
    </header>

    <main class="text-justify">
        <?php
        //Truncates the text when necessary. The text will be truncated to the
        //  location of the `<!-- readmore -->` tag. If the tag is not present,
        //  the value in the configuration will be used
        $text = $page->get('text');
        if (!$this->getRequest()->isAction(['view', 'preview'])) {
            $strpos = strpos($text, '<!-- read-more -->');
            $truncatedOptions = ['ellipsis' => ''];
            if (!$strpos) {
                $strpos = getConfigOrFail('default.truncate_to');
                $truncatedOptions = ['html' => true];
            }
            $text = $truncatedText = $this->Text->truncate($text, $strpos, $truncatedOptions);
        }
        echo $text;
        ?>
    </main>

    <?php if (isset($truncatedText) && $truncatedText !== $text) : ?>
    <div class="buttons mt-2 text-right">
        <?= $this->Html->button(__d('me_cms', 'Read more'), $page->get('url'), ['class' => ' readmore']) ?>
    </div>
    <?php endif; ?>

    <?php if (getConfig('page.shareaholic') && $isView) : ?>
    <div class="mt-3">
        <?= $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id')) ?>
    </div>
    <?php endif; ?>

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
