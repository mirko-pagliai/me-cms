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
?>

<div id="accordionSidebar" class="accordion accordion-flush">
    <?php foreach ($this->getAllMenuHelpers() as $Helper) : ?>
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading-<?= $Helper->getName() ?>">
            <?php
            $options = optionsParser($Helper->getOptions())->add([
                'class' => 'accordion-button collapsed p-3',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#collapse-' . $Helper->getName(),
                'aria-expanded' => 'false',
                'aria-controls' => 'collapse-' . $Helper->getName(),
            ]);

            echo $this->Html->link($Helper->getTitle(), '#collapse-' . $Helper->getName(), $options->toArray());
            ?>
        </h2>
        <div id="collapse-<?= $Helper->getName() ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $Helper->getName() ?>" data-bs-parent="#accordionSidebar">
            <div class="accordion-body my-2 p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($Helper->getLinks() as $link) : ?>
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
