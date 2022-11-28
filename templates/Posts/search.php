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
 *
 * @var string $pattern
 * @var \MeCms\Model\Entity\Post[] $posts
 * @var \MeCms\View\View\AppView $this
 */

$this->extend('/common/index');
$this->assign('title', $title = __d('me_cms', 'Search posts'));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title, ['_name' => 'postsSearch']);

echo $this->Form->create(null, ['type' => 'get', 'url' => ['_name' => 'postsSearch']]);
echo $this->Form->control('p', [
    'append-text' => $this->Form->button(__d('me_cms', 'Search'), ['class' => 'btn-primary', 'icon' => 'search', 'type' => 'submit']),
    'default' => $this->getRequest()->getQuery('p'),
    'label' => false,
    'placeholder' => sprintf('%s...', __d('me_cms', 'Search')),
]);
echo $this->Form->end();
?>

<?php if ($pattern) : ?>
    <div class="bg-info text-white mt-3 mb-3 p-2">
        <em><?= __d('me_cms', 'You have searched for: {0}', $pattern) ?></em>
    </div>
<?php endif; ?>

<?php if ($posts) : ?>
    <div class="as-table">
        <?php foreach ($posts as $post) : ?>
            <div class="mb-3 p-1">
                <h6>
                    <?= $this->Html->link($post->get('title'), $post->get('url')) ?>
                    <span class="small text-muted">
                        (<?= $post->get('created')->i18nFormat(getConfigOrFail('main.datetime.short')) ?>)
                    </span>
                </h6>

                <div class="text-justify">
                <?php
                    //Extracts an excerpt from `$pattern` and highlights `$pattern`
                    $text = $this->Text->excerpt($post->get('plain_text'), $pattern, 350);
                    echo $this->Text->highlight($text, $pattern, ['format' => '<mark>\1</mark>']);
                ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= $this->element('MeTools.paginator') ?>
<?php endif; ?>
