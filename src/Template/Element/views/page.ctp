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
        <?php if (getConfig('page.category') && $page->category->title && $page->category->slug) : ?>
            <h5 class="category mb-1">
                <?= $this->Html->link($page->category->title, ['_name' => 'pagesCategory', $page->category->slug]) ?>
            </h5>
        <?php endif; ?>

        <h3 class="title mb-1">
            <?= $this->Html->link($page->title, ['_name' => 'page', $page->slug]) ?>
        </h3>

        <?php if ($page->subtitle) : ?>
            <h4 class="subtitle mb-1">
                <?= $this->Html->link($page->subtitle, ['_name' => 'page', $page->slug]) ?>
            </h4>
        <?php endif; ?>

        <div class="info mt-2 text-muted">
            <?php
            if (getConfig('page.created')) {
                echo $this->Html->time(
                    __d('me_cms', 'Posted on {0}', $page->created->i18nFormat()),
                    ['class' => 'date', 'icon' => 'clock-o']
                );
            }
            ?>
        </div>
    </div>
    <div class="content text-justify">
        <?php
        //Executes BBCode on the text
        $text = $this->BBCode->parser($page->text);

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

    <div class="buttons mt-2 text-right">
        <?php
        //If it was requested to truncate the text and that has been
        //truncated, it shows the "Read more" link
        if (!empty($truncatedText) && $truncatedText !== $page->text) {
            echo $this->Html->button(
                __d('me_cms', 'Read more'),
                ['_name' => 'page', $page->slug],
                ['class' => ' readmore']
            );
        }
        ?>
    </div>
    <?php
    if (getConfig('page.shareaholic') && $this->request->isAction('view', 'Pages') && !$this->request->isAjax()) {
        echo $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id'));
    }
    ?>
</article>