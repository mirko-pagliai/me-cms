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
                    ['class' => 'date', 'icon' => 'clock-o']
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