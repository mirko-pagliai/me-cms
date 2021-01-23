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
 * @since       2.26.0
 */

namespace MeCms\Command\Install;

use MeCms\Command\Install\CopyConfigCommand;
use MeCms\Command\Install\CreateAdminCommand;
use MeCms\Command\Install\CreateGroupsCommand;
use MeCms\Command\Install\CreateSamplePostCommand;
use MeCms\Command\Install\FixElFinderCommand;
use MeCms\Command\VersionUpdatesCommand;
use MeTools\Command\Install\RunAllCommand as BaseRunAllCommand;

/**
 * Copies the configuration files
 */
class RunAllCommand extends BaseRunAllCommand
{
    /**
     * Constructor
     * @uses $questions
     */
    public function __construct()
    {
        parent::__construct();

        $this->questions = array_merge($this->questions, [
            [
                'question' => __d('me_tools', 'Copy configuration files?'),
                'default' => 'Y',
                'command' => CopyConfigCommand::class,
            ],
            [
                'question' => __d('me_tools', 'Fix {0}?', 'ElFinder'),
                'default' => 'Y',
                'command' => FixElFinderCommand::class,
            ],
            [
                'question' => __d('me_cms', 'Updates to the database or files needed for versioning?'),
                'default' => 'Y',
                'command' => VersionUpdatesCommand::class,
            ],
            [
                'question' => __d('me_cms', 'Create the user groups?'),
                'default' => 'N',
                'command' => CreateGroupsCommand::class,
            ],
            [
                'question' => __d('me_cms', 'Create an admin user?'),
                'default' => 'N',
                'command' => CreateAdminCommand::class,
            ],
            [
                'question' => __d('me_cms', 'Create a sample post?'),
                'default' => 'N',
                'command' => CreateSamplePostCommand::class,
            ],
        ]);
    }
}
