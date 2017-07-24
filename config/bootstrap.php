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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
/**
 * (here `Cake\Core\Plugin` is used, as the plugins are not yet all loaded)
 */
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\Routing\DispatcherFactory;

$request = new Request;

/**
 * Requires the base of bootstrap
 */
require_once __DIR__ . DS . 'bootstrap_base.php';

/**
 * Loads DebugKit, if debugging is enabled
 */
if (getConfig('debug') && !Plugin::loaded('DebugKit')) {
    Plugin::load('DebugKit', ['bootstrap' => true]);
}

/**
 * Loads other plugins
 */
Plugin::load('Thumber', ['bootstrap' => true, 'routes' => true]);
Plugin::load('Tokens', ['bootstrap' => true]);
Plugin::load('DatabaseBackup', ['bootstrap' => true]);
Plugin::load('WyriHaximus/MinifyHtml', ['bootstrap' => true]);
Plugin::load('Gourmet/CommonMark');
Plugin::load('Recaptcha');
Plugin::load('RecaptchaMailhide', ['bootstrap' => true, 'routes' => true]);

if (!getConfig(DATABASE_BACKUP . '.mailSender')) {
    Configure::write(DATABASE_BACKUP . '.mailSender', getConfigOrFail(ME_CMS . '.email.webmaster'));
}

//CakePHP will automatically set the locale based on the current user
DispatcherFactory::add('LocaleSelector');
