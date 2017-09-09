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

<div class="page-container content-container">
    <div class="content-header">
        <?php if (getConfig('page.category') && $page->category->title && $page->category->slug) : ?>
            <h5 class="content-category">
                <?= $this->Html->link($page->category->title, ['_name' => 'pagesCategory', $page->category->slug]) ?>
            </h5>
        <?php endif; ?>

        <h3 class="content-title">
            <?= $this->Html->link($page->title, ['_name' => 'page', $page->slug]) ?>
        </h3>

        <?php if ($page->subtitle) : ?>
            <h4 class="content-subtitle">
                <?= $this->Html->link($page->subtitle, ['_name' => 'page', $page->slug]) ?>
            </h4>
        <?php endif; ?>

        <div class="content-info">
            <?php if (getConfig('page.created')) : ?>
                <?= $this->Html->div(
                    'content-date',
                    __d('me_cms', 'Posted on {0}', $page->created->i18nFormat()),
                    ['icon' => 'clock-o']
                ) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="content-text">
        <?php
        //Executes BBCode on the text
        $text = $this->BBCode->parser($page->text);

        //Truncates the text if the "<!-- read-more -->" tag is present
        $strpos = strpos($text, '<!-- read-more -->');

        if (!$this->request->isAction(['view', 'preview']) && $strpos) {
            echo $truncatedText = $this->Text->truncate($text, $strpos, [
                'ellipsis' => false,
                'exact' => true,
                'html' => false,
            ]);
        //Truncates the text if requested by the configuration
        } elseif (!$this->request->isAction(['view', 'preview'])) {
            $truncatedText = $this->Text->truncate(
                $text,
                getConfigOrFail('default.truncate_to'),
                ['exact' => false, 'html' => true]
            );

            echo $truncatedText;
        } else {
            echo $text;
        }
        ?>
    </div>

    <div class="content-buttons">
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
</div>