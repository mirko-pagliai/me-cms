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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license	http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */

use Cake\Core\Configure;
use Cake\Error\Debugger;

if (Configure::read('debug')):
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.ctp');

    $this->start('file');
?>

<?php if (!empty($error->queryString)) : ?>
    <p class="notice"><strong>SQL Query:</strong>: <?= h($error->queryString) ?></p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
        <strong>SQL Query Params</strong>: <?= Debugger::dump($error->params) ?>
<?php endif; ?>
<?php
    echo $this->element('auto_table_warning');

    if(extension_loaded('xdebug'))
        xdebug_print_function_stack();

    $this->end();
endif;
?>
<h2><?= __d('me_cms', 'An internal error has occurred') ?></h2>
<p class="error"><strong><?= __d('me_cms', 'Error') ?></strong>: <?= h($message) ?></p>