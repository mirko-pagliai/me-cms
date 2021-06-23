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
use MeCms\Model\Entity\Post;
use MeTools\Console\Command;

/**
 * Creates a sample post
 * @property \MeCms\Model\Table\PostsTable $Posts
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class CreateSamplePostCommand extends Command
{
    /**
     * Hook method invoked by CakePHP when a command is about to be executed
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadModel('MeCms.Posts');
        $this->loadModel('MeCms.Users');
    }

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
        if (!$this->Posts->find()->isEmpty()) {
            $io->verbose(__d('me_cms', 'At least one post already exists'));

            return null;
        }

        /** @var \MeCms\Model\Entity\User $user */
        $user = $this->Users->find('all', ['fields' => ['id']])->first();
        if (!$user) {
            return $io->error(__d('me_cms', 'You must first create a user. Run the `{0}` command', 'bin/cake me_cms.create_admin'));
        }

        $post = new Post([
            'user_id' => $user->get('id'),
            'title' => 'This is sample post',
            'subtitle' => 'Just a sample post',
            'slug' => 'a-sample-post',
            'text' => 'Hi! This is just <strong>a sample post</strong>, automatically created during installation.<br />Welcome!',
        ]);
        if (!$this->Posts->save($post)) {
            return $io->error(__d('me_cms', I18N_OPERATION_NOT_OK));
        }

        $io->verbose('The sample post has been created');

        return null;
    }
}
