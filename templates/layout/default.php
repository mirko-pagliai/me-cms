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

use Cake\I18n\I18n;

/**
 * @var \MeCms\View\View\AppView $this
 */
$sidebar = $this->fetch('sidebar') . $this->Widget->all();
?>
<!DOCTYPE html>
<html lang="<?= substr(I18n::getLocale(), 0, 2) ?>">
    <head>
        <?php
        echo $this->Html->charset();
        echo $this->Html->viewport();
        echo $this->Html->title($this->fetch('title'));
        echo $this->fetch('meta');

        //CSS files which contain relative paths (and therefore should not be used with `AssetHelper`)
        echo $this->Html->css([
            '/vendor/font-awesome/css/all.min',
            'MeCms.fonts',
        ], ['block' => true]);
        //Default css and application css files to load automatically (default `contents` and `layout`)
        echo $this->Asset->css([
            '/vendor/bootstrap/css/bootstrap.min',
            'MeTools.default',
            'MeTools.forms',
            'MeCms.cookies',
            'MeCms.layout',
            'MeCms.contents',
            ...(array)getConfig('default.other_css'),
        ], ['block' => true]);

        //Other css files
        echo $this->Asset->css('MeCms.print', ['block' => true, 'media' => 'print']);
        echo $this->fetch('css');

        //Default script and application script files to load automatically (default empty)
        echo $this->Asset->script([
            '/vendor/jquery/jquery.min',
            '/vendor/bootstrap/js/bootstrap.bundle.min',
            'MeCms.js.cookie.min',
            'MeTools.default',
            ...(array)getConfig('default.other_js'),
        ], ['block' => true]);
        echo $this->fetch('script');
        ?>
    </head>
    <body class="d-flex flex-column min-vh-100">
        <header>
            <?php
            $logo = $this->Html->h1(getConfigOrFail('main.title'));
            if (is_readable(WWW_ROOT . 'img' . DS . getConfig('default.logo'))) {
                $logo = $this->Html->image(getConfig('default.logo'));
            }
            echo $this->Html->link($logo, '/', ['class' => 'd-block mx-4 my-5 text-center', 'title' => __d('me_cms', 'Homepage')]);

            //It uses the cache only if debugging is disabled.
            //It will use the `topbar.php` element if it is present in the app, otherwise it will use the plugin one
            $topbarOptions = getConfig('debug') ? [] : ['cache' => ['key' => 'topbar']];
            if ($this->elementExistsInApp('topbar')) {
                $topbarName = 'topbar';
                $topbarOptions += ['plugin' => false];
            }
            echo $this->element($topbarName ?? 'MeCms.topbar', [], $topbarOptions);
            ?>
        </header>
        <div class="container flex-grow-1 mt-5">
            <div class="row">
                <main class="col">
                    <?php
                    echo $this->Flash->render();

                    if ($this->Breadcrumbs->render()) {
                        $this->Breadcrumbs->prepend(__d('me_cms', 'Home'), '/');
                        echo $this->Breadcrumbs->render();
                    }

                    echo $this->fetch('content');
                    ?>
                </main>
            <?php if ($sidebar) : ?>
                <nav id="sidebar" class="col-lg-3">
                    <?= $this->fetch('sidebar') ?>
                    <?= $this->Widget->all() ?>
                </nav>
            <?php endif; ?>
            </div>
        </div>
        <footer class="p-4 small text-center">
            <?php
            //It uses the cache only if debugging is disabled.
            //It will use the `footer.php` element if it is present in the app, otherwise it will use the plugin one
            $footerOptions = getConfig('debug') ? [] : ['cache' => ['key' => 'footer']];
            if ($this->elementExistsInApp('footer')) {
                $footerName = 'footer';
                $footerOptions += ['plugin' => false];
            }
            echo $this->element($footerName ?? 'MeCms.footer', [], $footerOptions);
            ?>
        </footer>
        <?= $this->fetch('css_bottom') ?>
        <?= $this->fetch('script_bottom') ?>
    </body>
</html>
