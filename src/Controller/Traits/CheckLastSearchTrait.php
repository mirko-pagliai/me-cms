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
 * @since       2.16.1
 */
namespace MeCms\Controller\Traits;

/**
 * This trait provides a method to check if the latest search has been executed
 *  out of the minimum interval
 */
trait CheckLastSearchTrait
{
    /**
     * Checks if the latest search has been executed out of the minimum
     *  interval
     * @param string $queryId Query
     * @return bool
     */
    protected function checkLastSearch($queryId = false)
    {
        $interval = getConfig('security.search_interval');

        if (!$interval) {
            return true;
        }

        if ($queryId) {
            $queryId = md5($queryId);
        }

        $lastSearch = $this->request->session()->read('last_search');

        if ($lastSearch) {
            //Checks if it's the same search
            if ($queryId && !empty($lastSearch['id']) && $queryId === $lastSearch['id']) {
                return true;
            //Checks if the interval has not yet expired
            } elseif (($lastSearch['time'] + $interval) > time()) {
                return false;
            }
        }

        $this->request->session()->write('last_search', [
            'id' => $queryId,
            'time' => time(),
        ]);

        return true;
    }
}
