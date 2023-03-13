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
use MeCms\Model\Entity\Post;

/**
 * @var \MeCms\Model\Entity\Post $post
 * @var \Cake\Collection\Collection<\MeCms\Model\Entity\Post> $related
 * @var \MeCms\View\View\AppView $this
 */

$this->extend('/common/view');
$this->assign('title', $post->get('title'));

/**
 * Breadcrumb
 */
if (getConfig('post.category')) {
    $this->Breadcrumbs->add(
        $post->get('category')->get('title'),
        ['_name' => 'postsCategory', $post->get('category')->get('slug')]
    );
}
$this->Breadcrumbs->add($post->get('title'), ['_name' => 'post', $post->get('slug')]);

/**
 * Meta tags
 */
if ($this->getRequest()->is('action', 'view', 'Posts')) {
    $this->Html->meta(['content' => 'article', 'property' => 'og:type']);

    if ($post->hasValue('modified')) {
        $this->Html->meta(['content' => $post->get('modified')->toUnixString(), 'property' => 'og:updated_time']);
    }

    //Adds tags as keywords
    if (getConfig('post.keywords')) {
        $this->Html->meta('keywords', preg_replace('/,\s/', ',', $post->get('tags_as_string')));
    }

    if ($post->hasValue('preview')) {
        foreach ($post->get('preview') as $preview) {
            $this->Html->meta(['href' => $preview->get('url'), 'rel' => 'image_src']);
            $this->Html->meta(['content' => $preview->get('url'), 'property' => 'og:image']);
            $this->Html->meta(['content' => $preview->get('width'), 'property' => 'og:image:width']);
            $this->Html->meta(['content' => $preview->get('height'), 'property' => 'og:image:height']);
        }
    }

    $this->Html->meta([
        'content' => $this->Text->truncate($post->get('plain_text'), 100, ['html' => true]),
        'property' => 'og:description',
    ]);
}

echo $this->element('views/post', compact('post'));
?>

<?php if (!$related->isEmpty()) : ?>
    <?php
        $relatedAsLinks = $related->map(fn(Post $post): string => $this->Html->link($post->get('title'), ['_name' => 'post', $post->get('slug')]))
            ->toArray();
    ?>
    <div class="related-contents mb-4">
        <?= $this->Html->h5(__d('me_cms', 'Related posts')) ?>
        <?php if (!getConfig('post.related.images')) : ?>
            <?= $this->Html->ul($relatedAsLinks, ['icon' => 'caret-right']) ?>
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
                <?= $this->Html->ul($relatedAsLinks, ['icon' => 'caret-right']) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
