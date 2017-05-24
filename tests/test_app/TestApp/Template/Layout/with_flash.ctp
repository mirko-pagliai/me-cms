<?php $this->loadHelper('Html'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?= $this->Html->charset(); ?>
    <title><?= $this->fetch('title'); ?></title>
    <?= $this->Html->meta('icon'); ?>
    <?= $this->Html->css('cake.generic'); ?>
    <?= $this->fetch('script'); ?>
    ?>
</head>
<body>
    <div id="container">
        <div id="content">
            <?= $this->Flash->render(); ?>
            <?= $this->fetch('content'); ?>
        </div>
    </div>
</body>
</html>