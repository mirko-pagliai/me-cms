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

//Adds tags as keywords meta-tag
if (config('post.keywords') &&
    $this->request->is('action', 'view', 'Posts') &&
    !empty($post->tags_as_string)
) {
    $this->Html->meta('keywords', preg_replace('/,\s/', ',', $post->tags_as_string));
}
?>

<div class="post-container content-container">
    <div class="content-header">
        <?php if (config('post.category') && !empty($post->category->title) && !empty($post->category->slug)) : ?>
            <h5 class="content-category">
                <?php
                    echo $this->Html->link(
                        $post->category->title,
                        ['_name' => 'postsCategory', $post->category->slug]
                    );
                ?>
            </h5>
        <?php endif; ?>

        <h3 class="content-title">
            <?php
                echo $this->Html->link(
                    $post->title,
                    ['_name' => 'post', $post->slug]
                );
            ?>
        </h3>

        <?php if ($post->subtitle) : ?>
            <h4 class="content-subtitle">
                <?php
                    echo $this->Html->link(
                        $post->subtitle,
                        ['_name' => 'post', $post->slug]
                    );
                ?>
            </h4>
        <?php endif; ?>

        <div class="content-info">
            <?php
            if (config('post.author')) {
                echo $this->Html->div(
                    'content-author',
                    __d('me_cms', 'Posted by {0}', $post->user->full_name),
                    ['icon' => 'user']
                );
            }

            if (config('post.created')) {
                echo $this->Html->div(
                    'content-date',
                    __d(
                        'me_cms',
                        'Posted on {0}',
                        $post->created->i18nFormat(config('main.datetime.long'))
                    ),
                    ['icon' => 'clock-o']
                );
            }
            ?>
        </div>
    </div>

    <div class="content-text clearfix">
        <?php
        //Executes BBCode on the text
        $text = $this->BBCode->parser($post->text);

        //Truncates the text if the "<!-- read-more -->" tag is present
        $strpos = strpos($text, '<!-- read-more -->');

        if (!$this->request->is('action', 'view', 'Posts') && $strpos) {
            echo $truncatedText = $this->Text->truncate($text, $strpos, [
                'ellipsis' => false,
                'exact' => true,
                'html' => false,
            ]);
        //Truncates the text if requested by the configuration
        } elseif (!$this->request->is('action', 'view', 'Posts') &&
            config('default.truncate_to')
        ) {
            echo $truncatedText = $this->Text->truncate($text, config('default.truncate_to'), [
                'exact' => false,
                'html' => true,
            ]);
        } else {
            echo $text;
        }
        ?>
    </div>

    <?php if (config('post.tags') && $post->tags) : ?>
        <div class="content-tags">
            <?php
            foreach ($post->tags as $tag) {
                echo $this->Html->link(
                    $tag->tag,
                    ['_name' => 'postsTag', $tag->slug],
                    ['icon' => 'tags']
                );
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="content-buttons">
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
    if (config('post.shareaholic') &&
        config('shareaholic.app_id') &&
        $this->request->is('action', 'view', 'Posts')
        && !$this->request->isAjax()
    ) {
        echo $this->Html->shareaholic(config('shareaholic.app_id'));
    }
    ?>
</div>

<?php if (!empty($related)) : ?>
    <div class="related-contents">
        <?= $this->Html->h4(__d('me_cms', 'Related posts')) ?>
        <?php if (!config('post.related.images')) : ?>
            <?php
                echo $this->Html->ul(array_map(function ($post) {
                    return $this->Html->link(
                        $post->title,
                        ['_name' => 'post', $post->slug]
                    );
                }, $related), ['icon' => 'caret-right']);
            ?>
        <?php else : ?>
            <div class="visible-xs">
                <?php
                    echo $this->Html->ul(array_map(function ($post) {
                        return $this->Html->link(
                            $post->title,
                            ['_name' => 'post', $post->slug]
                        );
                    }, $related), ['icon' => 'caret-right']);
                ?>
            </div>

            <div class="hidden-xs row">
                <?php foreach ($related as $post) : ?>
                    <div class="col-sm-6 col-md-3">
                        <?php
                            echo $this->element(
                                'views/post-preview',
                                compact('post')
                            );
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>