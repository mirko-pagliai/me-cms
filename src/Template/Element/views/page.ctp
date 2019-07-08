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
$isView = $this->request->isAction('view', 'Pages') && !$this->request->isAjax();
?>

<article class="clearfix mb-4">
    <header class="mb-3">
        <?php if (getConfig('page.category') && $page->has('category') && $page->category->has(['slug', 'title'])) : ?>
            <h5 class="category mb-2">
                <?= $this->Html->link($page->category->title, ['_name' => 'pagesCategory', $page->category->slug]) ?>
            </h5>
        <?php endif; ?>

        <h3 class="title mb-2">
            <?= $this->Html->link($page->title, ['_name' => 'page', $page->slug]) ?>
        </h3>

        <?php if ($page->has('subtitle')) : ?>
            <h4 class="subtitle mb-2">
                <?= $this->Html->link($page->subtitle, ['_name' => 'page', $page->slug]) ?>
            </h4>
        <?php endif; ?>

        <div class="info">
            <?php
            if (getConfig('page.created') && $page->has('created')) {
                echo $this->Html->time(
                    __d('me_cms', 'Posted on {0}', $page->created->i18nFormat()),
                    ['class' => 'date', 'icon' => 'clock']
                );
            }
            ?>
        </div>
    </header>

    <main class="text-justify">
        <?php
        //Executes BBCode on the text
        $text = $this->BBCode->parser($page->text);

        //Truncates the text when necessary. The text will be truncated to the
        //  location of the `<!-- readmore -->` tag. If the tag is not present,
        //  the value in the configuration will be used
        if (!$this->request->isAction(['view', 'preview'])) {
            $strpos = strpos($text, '<!-- read-more -->');
            $truncatedOptions = ['ellipsis' => false];

            if (!$strpos) {
                $strpos = getConfigOrFail('default.truncate_to');
                $truncatedOptions = ['html' => true];
            }

            $truncatedText = $this->Text->truncate($text, $strpos, $truncatedOptions);
            echo $truncatedText;
        } else {
            echo $text;
        }
        ?>
    </main>

    <?php if (!empty($truncatedText) && $truncatedText !== $page->text) : ?>
    <div class="buttons mt-2 text-right">
        <?= $this->Html->button(__d('me_cms', 'Read more'), ['_name' => 'page', $page->slug], ['class' => ' readmore']) ?>
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
    this.page.url = '<?= $this->Url->build(['_name' => 'page', $page->slug], true) ?>';
    this.page.identifier = '<?= sprintf('page-#%s', $page->id) ?>';
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