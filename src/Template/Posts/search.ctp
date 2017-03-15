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
$this->extend('/Common/index');
$this->assign('title', $title = __d('me_cms', 'Search posts'));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title, ['_name' => 'postsSearch']);

echo $this->Form->create(null, [
    'type' => 'get',
    'url' => ['_name' => 'postsSearch']
]);
echo $this->Form->input('p', [
    'default' => $this->request->query('p'),
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
    <div class="bg-info margin-20 padding-10">
        <?= __d('me_cms', 'You have searched for: {0}', $this->Html->em($pattern)) ?>
    </div>
<?php endif; ?>

<?php if (!empty($posts)) : ?>
    <div class="as-table">
        <?php foreach ($posts as $post) : ?>
            <div class="margin-10 padding-10">
                <?= $this->Html->link($post->title, ['_name' => 'post', $post->slug]) ?>
                <span class="small text-muted">
                    (<?= $post->created->i18nFormat(config('main.datetime.short')) ?>)
                </span>
                <div class="text-justify">
                    <?php
                        //Executes BBCode on the text
                        $post->text = $this->BBCode->parser($post->text);

                        echo $this->Text->truncate(
                            strip_tags($post->text),
                            350,
                            ['exact' => false, 'html' => true]
                        );
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= $this->element('MeTools.paginator') ?>
<?php endif; ?>