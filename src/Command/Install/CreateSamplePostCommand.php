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
 * @since       2.30.1
 */

namespace MeCms\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\Exception\RecordNotFoundException;
use MeTools\Console\Command;

/**
 * Creates a sample post
 */
class CreateSamplePostCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Creates a sample post'));
    }

    /**
     * Creates a sample post
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        /** @var \MeCms\Model\Table\PostsTable $Posts */
        $Posts = $this->getTableLocator()->get('MeCms.Posts');

        if (!$Posts->find()->all()->isEmpty()) {
            $io->verbose(__d('me_cms', 'At least one post already exists'));

            return null;
        }

        try {
            /** @var \MeCms\Model\Entity\User $User */
            $User = $Posts->Users->find()->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return $io->error(__d('me_cms', 'You must first create a user. Run the `{0}` command', 'bin/cake me_cms.create_admin'));
        }

        /** @var \MeCms\Model\Entity\PostsCategory $Category */
        $Category = $Posts->Categories->find()->first();
        if (!$Category) {
            $Category = $Posts->Categories->newEntity(['title' => 'Example of category', 'slug' => 'example-of-category']);
            $Posts->Categories->saveOrFail($Category);
        }

        $data = [
            'category_id' => $Category->get('id'),
            'user_id' => $User->get('id'),
            'title' => 'This is sample post',
            'subtitle' => 'Just a sample post',
            'slug' => 'a-sample-post',
            'text' => 'Hi! This is just <strong>a sample post</strong>, automatically created during installation.<br />Welcome!',
        ];
        if (!$Posts->save($Posts->newEntity($data))) {
            return $io->error(I18N_OPERATION_NOT_OK);
        }

        $io->verbose('The sample post has been created');

        return null;
    }
}
