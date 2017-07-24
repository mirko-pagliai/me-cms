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
use Cake\Network\Request;

/**
 * Adds `is('add')`, `is('delete')`, `is('edit')`, `is('index')` and
 *  `is('view')` detectors.
 *
 * They check if the specified action is the current action.
 */
foreach (['add', 'delete', 'edit', 'index', 'view'] as $action) {
    Request::addDetector($action, function ($request) use ($action) {
        return $request->is('action', $action);
    });
}

/**
 * Adds `is('admin')` detector.
 *
 * It checks if the specified prefix has the `admin` prefix.
 *
 * Example:
 * <code>
 * $this->request->isAdmin();
 * </code>
 */
Request::addDetector('admin', function ($request) {
    return $request->getParam('prefix') === ADMIN_PREFIX;
});

/**
 * Adds `is('banned')` detector.
 *
 * It checks if the user's IP address is banned.
 *
 * Example:
 * <code>
 * $this->request->isBanned();
 * </code>
 */
Request::addDetector('banned', function ($request) {
    $banned = getConfig('Banned');

    //The IP address is allowed if:
    //  - the list of banned IP is empty;
    //  - is localhost;
    //  - the IP address has already been verified.
    if (!$banned || $request->is('localhost') || $request->session()->read('allowed_ip')) {
        return false;
    }

    //Replaces asteriskes
    $banned = preg_replace('/\\\\\*/', '[0-9]{1,3}', array_map('preg_quote', (array)$banned));

    if (preg_match(sprintf('/^(%s)$/', implode('|', $banned)), $request->clientIp())) {
        return true;
    }

    //In any other case, saves the result in the session
    $request->session()->write('allowed_ip', true);

    return false;
});

/**
 * Adds `is('offline')` detector.
 *
 * It checks if the site is offline.
 *
 * Example:
 * <code>
 * $this->request->isOffline();
 * </code>
 */
Request::addDetector('offline', function ($request) {
    if (!getConfig('default.offline')) {
        return false;
    }

    //Always online for admin requests
    if ($request->is('admin')) {
        return false;
    }

    //Always online for some actions
    if ($request->is('action', ['offline', 'login', 'logout'])) {
        return false;
    }

    return true;
});
