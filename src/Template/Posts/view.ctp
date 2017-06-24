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
$this->extend('/Common/view');
$this->assign('title', $post->title);

/**
 * Userbar
 */
if (!$post->active) {
    $this->userbar($this->Html->span(__d('me_cms', 'Draft'), ['class' => 'label label-warning']));
}
if ($post->created->isFuture()) {
    $this->userbar($this->Html->span(__d('me_cms', 'Scheduled'), ['class' => 'label label-warning']));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit post'),
    ['action' => 'edit', $post->id, 'prefix' => ADMIN_PREFIX],
    ['icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete post'),
    ['action' => 'delete', $post->id, 'prefix' => ADMIN_PREFIX],
    [
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
        'target' => '_blank',
    ]
));

/**
 * Breadcrumb
 */
if (getConfig('post.category')) {
    $this->Breadcrumbs->add($post->category->title, ['_name' => 'postsCategory', $post->category->slug]);
}
$this->Breadcrumbs->add($post->title, ['_name' => 'post', $post->slug]);

/**
 * Meta tags
 */
if ($this->request->isAction('view', 'Posts')) {
    $this->Html->meta(['content' => 'article', 'property' => 'og:type']);
    $this->Html->meta(['content' => $post->modified->toUnixString(), 'property' => 'og:updated_time']);

    //Adds tags as keywords
    if (getConfig('post.keywords') && $post->tags_as_string) {
        $this->Html->meta('keywords', preg_replace('/,\s/', ',', $post->tags_as_string));
    }

    if ($post->preview) {
        $this->Html->meta(['href' => $post->preview['preview'], 'rel' => 'image_src']);
        $this->Html->meta(['content' => $post->preview['preview'], 'property' => 'og:image']);
        $this->Html->meta(['content' => $post->preview['width'], 'property' => 'og:image:width']);
        $this->Html->meta(['content' => $post->preview['height'], 'property' => 'og:image:height']);
    }

    if ($post->text) {
        $this->Html->meta([
            'content' => $this->Text->truncate(
                trim(strip_tags($this->BBCode->remove($post->text))),
                100,
                ['html' => true]
            ),
            'property' => 'og:description',
        ]);
    }
}

echo $this->element('views/post', compact('post'));
?>

<?php if (!empty($related)) : ?>
    <?php
        $relatedAsList = collection($related)->map(function ($post) {
            return $this->Html->link($post->title, ['_name' => 'post', $post->slug]);
        })->toList();
    ?>
    <div class="related-contents">
        <?= $this->Html->h4(__d('me_cms', 'Related posts')) ?>
        <?php if (!getConfig('post.related.images')) : ?>
            <?= $this->Html->ul($relatedAsList, ['icon' => 'caret-right']) ?>
        <?php else : ?>
            <div class="visible-xs">
                <?= $this->Html->ul($relatedAsList, ['icon' => 'caret-right']) ?>
            </div>

            <div class="hidden-xs row">
                <?php foreach ($related as $post) : ?>
                    <div class="col-sm-6 col-md-3">
                        <?= $this->element('views/post-preview', compact('post')) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>