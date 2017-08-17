<?php
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
// (here `Cake\Core\Plugin` is used, as the plugins are not yet all loaded)
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\Routing\DispatcherFactory;

$request = new Request;

//Requires the base of bootstrap
require_once __DIR__ . DS . 'bootstrap_base.php';

//Loads DebugKit, if debugging is enabled
if (getConfig('debug') && !Plugin::loaded('DebugKit')) {
    Plugin::load('DebugKit', ['bootstrap' => true]);
}

//Loads other plugins
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

//Sets the locale based on the current user
DispatcherFactory::add('LocaleSelector');
