<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<div class="page-container content-container">
    <div class="content-header">
        <?php if (config('page.category') && $page->category->title && $page->category->slug) : ?>
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
            <?php if (config('page.created')) : ?>
                <?= $this->Html->div('content-date', __d(
                    'me_cms',
                    'Posted on {0}',
                    $page->created->i18nFormat(config('main.datetime.long'))
                ), ['icon' => 'clock-o']) ?>
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
        } elseif (!$this->request->isAction(['view', 'preview']) && config('default.truncate_to')) {
            echo $truncatedText = $this->Text->truncate(
                $text,
                config('default.truncate_to'),
                ['exact' => false, 'html' => true]
            );
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
    if (config('page.shareaholic') && config('shareaholic.app_id') &&
        $this->request->isAction('view', 'Pages') && !$this->request->isAjax()
    ) {
        echo $this->Html->shareaholic(config('shareaholic.app_id'));
    }
    ?>
</div>