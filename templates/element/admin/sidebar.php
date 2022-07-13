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

use MeCms\Core\Plugin;

$plugins = Plugin::all(['mecms_core' => false]);
$pluginsMenus = array_merge(...array_map(fn(string $plugin): array => $this->MenuBuilder->generate($plugin), $plugins));
?>

<?php foreach ($pluginsMenus as $plugin => $menu): ?>
    <div class="card rounded-0 shadow-sm mb-2">
    <?php
        $titleOptions = optionsParser($menu['titleOptions'])->add([
            'class' => 'py-2 rounded-0 text-start',
            'data-bs-toggle' => 'collapse',
            'aria-expanded' => 'false',
            'aria-controls' => 'collapse-sidebar-' . slug($plugin),
        ])->addButtonClasses();

        echo $this->Html->button($menu['title'], '#collapse-sidebar-' . slug($plugin), $titleOptions->toArray());
    ?>

        <div class="collapse" data-bs-parent="#sidebar-cards" id="collapse-sidebar-<?= slug($plugin) ?>">
            <div class="card-body p-0">
            <?php
                $list = array_map(fn($link): string => call_user_func_array([$this->Html, 'link'], [...$link, ['class' => 'd-block px-3 py-2']]), $menu['links']);

                echo $this->Html->ul($list, ['class' => 'list-group list-group-flush'], ['class' => 'list-group-item p-0']);
            ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
