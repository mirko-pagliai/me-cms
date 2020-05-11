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
$this->extend('/Admin/common/index');
$this->assign('title', __d('me_cms', 'Media browser'));
$this->Asset->script('MeCms.admin/elfinder', ['block' => 'script_bottom']);

echo $this->Html->iframe($explorer, ['id' => 'file-explorer', 'width' => '100%']);
