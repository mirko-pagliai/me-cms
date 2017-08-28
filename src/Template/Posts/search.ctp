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
$this->extend('/Common/index');
$this->assign('title', $title = __d('me_cms', 'Search posts'));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title, ['_name' => 'postsSearch']);

echo $this->Form->create(null, ['type' => 'get', 'url' => ['_name' => 'postsSearch']]);
echo $this->Form->control('p', [
    'default' => $this->request->getQuery('p'),
    'label' => false,
    'placeholder' => sprintf('%s...', __d('me_cms', 'Search')),
]);
echo $this->Form->submit(__d('me_cms', 'Search'), [
    'class' => 'btn-primary visible-lg-inline',
    'icon' => 'search',
]);
echo $this->Form->end();
?>

<?php if (!empty($pattern)) : ?>
    <div class="bg-info mb-4 padding-10">
        <?= __d('me_cms', 'You have searched for: {0}', $this->Html->em($pattern)) ?>
    </div>
<?php endif; ?>

<?php if (!empty($posts)) : ?>
    <div class="as-table">
        <?php foreach ($posts as $post) : ?>
            <div class="margin-10 padding-10">
                <?= $this->Html->link($post->title, ['_name' => 'post', $post->slug]) ?>
                <span class="small text-muted">
                    (<?= $post->created->i18nFormat(getConfigOrFail('main.datetime.short')) ?>)
                </span>
                <div class="text-justify">
                <?php
                    //Executes BBCode on the text and strips other tags
                    $text = strip_tags($this->BBCode->parser($post->text));
                    echo $this->Text->truncate($text, 350, ['exact' => false, 'html' => true]);
                ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= $this->element('MeTools.paginator') ?>
<?php endif; ?>