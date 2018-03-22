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
$this->extend('/Common/view');
$this->assign('title', $post->title);

/**
 * Userbar
 */
$class = 'badge badge-warning';
if (!$post->active) {
    $this->userbar($this->Html->span(I18N_DRAFT, compact('class')));
}
if ($post->created->isFuture()) {
    $this->userbar($this->Html->span(I18N_SCHEDULED, compact('class')));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit post'),
    ['action' => 'edit', $post->id, 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link', 'icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete post'),
    ['action' => 'delete', $post->id, 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link text-danger', 'icon' => 'trash-o', 'confirm' => I18N_SURE_TO_DELETE, 'target' => '_blank']
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

    if ($post->has('modified')) {
        $this->Html->meta(['content' => $post->modified->toUnixString(), 'property' => 'og:updated_time']);
    }

    //Adds tags as keywords
    if (getConfig('post.keywords') && $post->has('tags_as_string')) {
        $this->Html->meta('keywords', preg_replace('/,\s/', ',', $post->tags_as_string));
    }

    if ($post->has('preview')) {
        foreach ($post->preview as $preview) {
            $this->Html->meta(['href' => $preview->url, 'rel' => 'image_src']);
            $this->Html->meta(['content' => $preview->url, 'property' => 'og:image']);
            $this->Html->meta(['content' => $preview->width, 'property' => 'og:image:width']);
            $this->Html->meta(['content' => $preview->height, 'property' => 'og:image:height']);
        }
    }

    if ($post->has('text')) {
        $this->Html->meta([
            'content' => $this->Text->truncate($post->plain_text, 100, ['html' => true]),
            'property' => 'og:description',
        ]);
    }
}

echo $this->element('views/post', compact('post'));
?>

<?php if (!empty($related)) : ?>
    <?php
        $relatedAsArray = collection($related)->map(function ($post) {
            return $this->Html->link($post->title, ['_name' => 'post', $post->slug]);
        })->toArray();
    ?>
    <div class="related-contents mb-4">
        <?= $this->Html->h5(__d('me_cms', 'Related posts')) ?>
        <?php if (!getConfig('post.related.images')) : ?>
            <?= $this->Html->ul($relatedAsArray, ['icon' => 'caret-right']) ?>
        <?php else : ?>
            <div class="d-none d-lg-block">
                <div class="row">
                    <?php foreach ($related as $post) : ?>
                        <div class="col-3">
                            <?= $this->element('views/post-preview', compact('post')) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="d-lg-none">
                <?= $this->Html->ul($relatedAsArray, ['icon' => 'caret-right']) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>