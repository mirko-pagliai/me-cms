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

use Cake\Http\ServerRequest;

/**
 * Adds `is('add')`, `is('delete')`, `is('edit')`, `is('index')` and
 *  `is('view')` detectors.
 *
 * They check if the specified action is the current action.
 */
foreach (['add', 'delete', 'edit', 'index', 'view'] as $action) {
    ServerRequest::addDetector($action, function (ServerRequest $request) use ($action) {
        return $request->isAction($action);
    });
}

/**
 * Adds `is('admin')` detector.
 *
 * It checks if the specified prefix has the `admin` prefix.
 *
 * Example:
 * <code>
 * $this->getRequest()->isAdmin();
 * </code>
 */
ServerRequest::addDetector('admin', function (ServerRequest $request) {
    return $request->getParam('prefix') === ADMIN_PREFIX;
});

/**
 * Adds `is('offline')` detector.
 *
 * It checks if the site is offline.
 *
 * Example:
 * <code>
 * $this->getRequest()->isOffline();
 * </code>
 */
ServerRequest::addDetector('offline', function (ServerRequest $request) {
    if (!getConfig('default.offline')) {
        return false;
    }

    //Always online for admin requests
    if ($request->is('admin')) {
        return false;
    }

    //Always online for some actions
    if ($request->isAction(['offline', 'login', 'logout'])) {
        return false;
    }

    return true;
});
