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
namespace MeCms\Shell;

use Cake\Core\Configure;
use MeCms\Shell\BaseUpdateShell;

/**
 * Applies updates
 */
class UpdateShell extends BaseUpdateShell
{
    /**
     * Updates to 2.14.7 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v14v7()
    {
        $this->loadModel('MeCms.Banners');

        //Adds "thumbnail" field to the banner table
        if (!$this->_checkColumn('thumbnail', $this->Banners->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `thumbnail` BOOLEAN NOT null DEFAULT true AFTER `active`;',
                $this->Banners->table()
            ));
        }
    }

    /**
     * Updates to 2.14.3 version
     * @return void
     */
    public function to2v14v3()
    {
        $dir = WWW_ROOT . 'assets';

        //Deletes `APP/webroot/assets`
        if (file_exists($dir)) {
            foreach (glob($dir . DS . '*', GLOB_NOSORT) as $file) {
                //@codingStandardsIgnoreLine
                @unlink($file);
            }

            //@codingStandardsIgnoreLine
            @\rmdir($dir);
        }

        //Creates `APP/tmp/assets`
        if (!file_exists(Configure::read('Assets.target'))) {
            mkdir(Configure::read('Assets.target'), 0777, true);
        }
    }

    /**
     * Updates to 2.14.0 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v14v0()
    {
        $this->loadModel('MeCms.BannersPositions');

        //Renames the "name" column as "title"
        if ($this->_checkColumn('name', $this->BannersPositions->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` CHANGE `name` `title` VARCHAR(100) NOT NULL;',
                $this->BannersPositions->table()
            ));
        }
    }

    /**
     * Updates to 2.10.1 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::$now
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     * @uses MeCms\Shell\BaseUpdateShell::_tableExists()
     */
    public function to2v10v1()
    {
        $this->loadModel('MeCms.Pages');

        $pages = $this->Pages->find('all');

        //Creates the `pages_categories` table and the first category
        if (!$this->_tableExists('pages_categories')) {
            $this->connection->execute(
                "CREATE TABLE `pages_categories` (
                    `id` int(11) NOT null,
                    `parent_id` int(11) DEFAULT null,
                    `lft` int(11) DEFAULT null,
                    `rght` int(11) DEFAULT null,
                    `title` varchar(100) NOT null,
                    `slug` varchar(100) NOT null,
                    `description` varchar(255) DEFAULT null,
                    `page_count` int(11) NOT null DEFAULT '0',
                    `created` datetime DEFAULT null,
                    `modified` datetime DEFAULT null
                );"
            );
            $this->connection->execute(
                "ALTER TABLE `pages_categories`
                ADD PRIMARY KEY (`id`),
                ADD UNIQUE KEY `title` (`title`),
                ADD UNIQUE KEY `slug` (`slug`);"
            );
            $this->connection->execute(
                "ALTER TABLE `pages_categories`
                MODIFY `id` int(11) NOT null AUTO_INCREMENT;"
            );

            //Creates the first category
            if (!$pages->isEmpty()) {
                $this->connection->execute(sprintf(
                    "INSERT INTO `%s` (`id`, `parent_id`, `lft`, `rght`, `title`, `slug`, `description`, `page_count`, `created`, `modified`) VALUES ('1', null, '1', '2', 'Main category', 'main-category', null, '0', '%s', '%s');",
                    $this->Pages->Categories->table(),
                    $this->now->i18nFormat('yyyy-MM-dd HH:mm:ss'),
                    $this->now->i18nFormat('yyyy-MM-dd HH:mm:ss')
                ));
            }
        }

        //Adds "category_id" field to the pages table
        if (!$this->_checkColumn('category_id', $this->Pages->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `category_id` INT(11) NOT null AFTER `id`;',
                $this->Pages->table()
            ));

            //Adds the pages to the first category
            if (!$pages->isEmpty()) {
                $this->Pages->query()->update()->set(['category_id' => '1'])->execute();

                //Updates the `page_count`
                $this->Pages->Categories->query()->update()
                    ->set(['page_count' => $pages->count()])
                    ->where(['id' => '1'])
                    ->execute();
            }
        }
    }

    /**
     * Updates to 2.10.0 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v10v0()
    {
        $this->loadModel('MeCms.Photos');

        //Adds "active" field to the photos table and sets the default value
        if (!$this->_checkColumn('active', $this->Photos->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `active` BOOLEAN NOT null DEFAULT true AFTER `description`;',
                $this->Photos->table()
            ));
            $this->Photos->query()->update()->set(['active' => true])->execute();
        }
    }

    /**
     * Updates to 2.7.0 version
     * @return void
     */
    public function to2v7v0()
    {
        $this->dispatchShell('MeCms.install', 'createVendorsLinks');

        $path = WWW_ROOT . 'vendor' . DS . 'jquery-cookie';

        if (is_link($path)) {
            //@codingStandardsIgnoreLine
            @unlink($path);
        }
    }

    /**
     * Updates to 2.6.0 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v6v0()
    {
        $this->loadModel('MeCms.BannersPositions');
        $this->loadModel('MeCms.PhotosAlbums');
        $this->loadModel('MeCms.PostsCategories');
        $this->loadModel('MeCms.Tags');
        $this->loadModel('MeCms.UsersGroups');

        //Adds "created" field to the banners positions table and sets the default value
        if (!$this->_checkColumn('created', $this->BannersPositions->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null AFTER `banner_count`;',
                $this->BannersPositions->table()
            ));
            $this->BannersPositions->query()->update()->set(['created' => $this->now])->execute();
        }

        //Adds "modified" field to the banners positions table and sets the default value
        if (!$this->_checkColumn('modified', $this->BannersPositions->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->BannersPositions->table()
            ));
            $this->BannersPositions->query()->update()->set(['modified' => $this->now])->execute();
        }

        //Adds "created" field to the photos albums table and sets the default value
        if (!$this->_checkColumn('created', $this->PhotosAlbums->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null AFTER `photo_count`;',
                $this->PhotosAlbums->table()
            ));
            $this->PhotosAlbums->query()->update()->set(['created' => $this->now])->execute();
        }

        //Adds "modified" field to the photos albums table and sets the default value
        if (!$this->_checkColumn('modified', $this->PhotosAlbums->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->PhotosAlbums->table()
            ));
            $this->PhotosAlbums->query()->update()->set(['modified' => $this->now])->execute();
        }

        //Adds "created" field to the posts categories table and sets the default value
        if (!$this->_checkColumn('created', $this->PostsCategories->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null AFTER `post_count`;',
                $this->PostsCategories->table()
            ));
            $this->PostsCategories->query()->update()->set(['created' => $this->now])->execute();
        }

        //Adds "modified" field to the posts categories table and sets the default value
        if (!$this->_checkColumn('modified', $this->PostsCategories->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->PostsCategories->table()
            ));
            $this->PostsCategories->query()->update()->set(['modified' => $this->now])->execute();
        }

        //Adds "created" field to the tags table and sets the default value
        if (!$this->_checkColumn('created', $this->Tags->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null AFTER `post_count`;',
                $this->Tags->table()
            ));
            $this->Tags->query()->update()->set(['created' => $this->now])->execute();
        }

        //Adds "modified" field to the tags table and sets the default value
        if (!$this->_checkColumn('modified', $this->Tags->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->Tags->table()
            ));
            $this->Tags->query()->update()->set(['modified' => $this->now])->execute();
        }

        //Adds "created" field to the users groups table and sets the default value
        if (!$this->_checkColumn('created', $this->UsersGroups->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null AFTER `user_count`;',
                $this->UsersGroups->table()
            ));
            $this->UsersGroups->query()->update()->set(['created' => $this->now])->execute();
        }

        //Adds "modified" field to the users groups table and sets the default value
        if (!$this->_checkColumn('modified', $this->UsersGroups->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->UsersGroups->table()
            ));
            $this->UsersGroups->query()->update()->set(['modified' => $this->now])->execute();
        }
    }

    /**
     * Updates to 2.2.1 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     */
    public function to2v2v1()
    {
        $this->loadModel('MeCms.Tags');

        //For each tag, it replaces the hyphen with space
        foreach ($this->Tags->find()->where(['tag LIKE' => '%-%'])->toArray() as $tag) {
            $this->Tags->query()->update()
                ->set(['tag' => str_replace('-', ' ', $tag->tag)])
                ->where(['id' => $tag->id])
                ->execute();
        }
    }

    /**
     * Updates to 2.1.9 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v1v9()
    {
        $this->loadModel('MeCms.Banners');
        $this->loadModel('MeCms.Photos');

        //Adds "created" field to the banners table and sets the default value
        if (!$this->_checkColumn('created', $this->Banners->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null AFTER `click_count`;',
                $this->Banners->table()
            ));
            $this->Banners->query()->update()->set(['created' => $this->now])->execute();
        }

        //Adds "modified" field to the banners table and sets the default value
        if (!$this->_checkColumn('modified', $this->Banners->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->Banners->table()
            ));
            $this->Banners->query()->update()->set(['modified' => $this->now])->execute();
        }

        //Adds "modified" field to the photos table and sets the default value
        if (!$this->_checkColumn('modified', $this->Photos->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `modified` DATETIME null AFTER `created`;',
                $this->Photos->table()
            ));
            $this->Photos->query()->update()->set(['modified' => $this->now])->execute();
        }
    }

    /**
     * Updates to 2.1.8 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v1v8()
    {
        $this->loadModel('MeCms.Photos');
        $this->loadModel('MeCms.Tags');

        //Deletes all unused tags
        $this->Tags->deleteAll(['post_count' => 0]);

        //For each tag, it replaces the hyphen with space
        foreach ($this->Tags->find()->toArray() as $tag) {
            $this->Tags->query()->update()
                ->set(['tag' => str_replace('-', ' ', $tag->tag)])
                ->where(['id' => $tag->id])
                ->execute();
        }

        //Adds "created" field to the photos table and sets the default value
        if (!$this->_checkColumn('created', $this->Photos->table())) {
            $this->connection->execute(sprintf(
                'ALTER TABLE `%s` ADD `created` DATETIME null DEFAULT null AFTER `description`;',
                $this->Photos->table()
            ));
            $this->Photos->query()->update()->set(['created' => $this->now])->execute();
        }
    }

    /**
     * Updates to 2.1.7 version
     * @return void
     * @uses MeCms\Shell\BaseUpdateShell::$connection
     */
    public function to2v1v7()
    {
        $this->loadModel('MeCms.Tags');

        $this->connection->execute(sprintf(
            'ALTER TABLE `%s` CHANGE `tag` `tag` VARCHAR(30) NOT null;',
            $this->Tags->table()
        ));
    }
}
