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
 * @var string $date
 * @var \Cake\I18n\FrozenDate $end
 * @var \Cake\I18n\FrozenDate $start
 * @var \MeCms\View\View\AppView $this
 */

$this->extend('/Posts/index');

if ($date === 'today') {
    $title = __d('me_cms', 'Posts of today');
} elseif ($date === 'yesterday') {
    $title = __d('me_cms', 'Posts of yesterday');
} else {
    [$year, $month, $day] = array_replace([null, null, null], explode('/', $date));

    $title = __dx('me_cms', 'posts of year', 'Posts of {0}', $start->i18nFormat('y'));
    if ($year && $month && $day) {
        $title = __dx('me_cms', 'posts of day', 'Posts of {0}', $start->i18nFormat(getConfigOrFail('main.date.long')));
    } elseif ($year && $month) {
        $title = __dx('me_cms', 'posts of month', 'Posts of {0}', $start->i18nFormat('MMMM y'));
    }
}
$this->assign('title', $title);

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title);
