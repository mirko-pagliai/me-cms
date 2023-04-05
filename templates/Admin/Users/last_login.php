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
 * @var \Cake\Collection\Collection<array> $loginLog
 * @var \MeCms\View\View\Admin\AppView $this
 */

$this->extend('/Admin/common/view');
$this->assign('title', $title = I18N_LAST_LOGIN);

if (!empty($loginLog)) {
    echo $this->element('admin/last-logins');
}
