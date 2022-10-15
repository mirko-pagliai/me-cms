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

use Cake\Utility\Inflector;
use MeCms\Core\Plugin;

$plugins = Plugin::all(['mecms_core' => false]);
$pluginsMenus = array_merge(...array_map(fn(string $plugin): array => $this->MenuBuilder->generate($plugin), $plugins));
$pluginsNames = array_map(fn($name): string => Inflector::camelize(str_replace(['/', '.'], '_', $name)), array_keys($pluginsMenus));
?>

<div class="accordion" id="accordionSidebar">
    <?php foreach (array_combine($pluginsNames, $pluginsMenus) as $name => $menu) : ?>
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= $name ?>">
            <?php
            $titleOptions = optionsParser($menu['titleOptions'])->add([
                'class' => 'accordion-button collapsed p-3',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#collapse' . $name,
                'aria-expanded' => 'false',
                'aria-controls' => 'collapse' . $name,
            ]);

            echo $this->Html->link($menu['title'], '#collapse' . $name, $titleOptions->toArray());
            ?>
        </h2>
        <div id="collapse<?= $name ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $name ?>" data-bs-parent="#accordionSidebar">
            <div class="accordion-body my-2 p-0">
                <?php
                $list = array_map(function (array $link): string {
                    return $this->Html->link($link[0], $link[1], ['class' => 'd-block px-3 py-2']);
                }, $menu['links']);

                echo $this->Html->ul($list, ['class' => 'list-group list-group-flush'], ['class' => 'list-group-item p-0']);
                ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
