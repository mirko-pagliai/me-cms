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
namespace MeCms\Controller;

use Cake\Database\Expression\QueryExpression;
use MeCms\Controller\AppController;

/**
 * Banners controller
 * @property \MeCms\Model\Table\BannersTable $Banners
 */
class BannersController extends AppController
{
    /**
     * Opens a banner (redirects to the banner target)
     * @param string $id Banner ID
     * @return \Cake\Network\Response|null
     */
    public function open($id)
    {
        $banner = $this->Banners->findActiveById($id)
            ->select(['target'])
            ->where(['target !=' => ''])
            ->cache(sprintf('view_%s', md5($id)), $this->Banners->getCacheName())
            ->firstOrFail();

        //Increases the click count
        $expression = new QueryExpression('click_count = click_count + 1');
        $this->Banners->updateAll([$expression], [compact('id')]);

        //Redirects
        return $this->redirect($banner->get('target'));
    }
}
