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
 * @var \MeCms\View\View\Admin\AppView $this
 */

use Cake\Utility\Inflector;
use MeCms\Core\Plugin;

$pluginsMenus = array_merge(...array_map(fn(string $plugin): array => $this->MenuBuilder->generate($plugin), Plugin::all(['mecms_core' => false])));
$pluginsNames = array_map(fn($name): string => Inflector::camelize(str_replace(['/', '.'], '_', $name)), array_keys($pluginsMenus));
?>

<div id="accordionSidebar" class="accordion accordion-flush">
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
                <ul class="list-group list-group-flush">
                <?php foreach ($menu['links'] as $link) : ?>
                    <li class="list-group-item p-0">
                        <?= $this->Html->link($link[0], $link[1], ['class' => 'd-block px-3 py-2']) ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
