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
namespace MeCms\Shell;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use MeTools\Console\Shell;

/**
 * UpdateShell.
 *
 * This shell provides subcommands to update your application
 */
class UpdateShell extends Shell
{
    /**
     * Updates `preview` field for `Posts` and `Pages` tables
     * @return void
     */
    public function updatePostsAndPagesPreviewField()
    {
        foreach (['Posts', 'Pages'] as $tableName) {
            $table = TableRegistry::get(sprintf('%s.%s', ME_CMS, $tableName));

            $records = $table->find()
                ->select(['id', 'text', 'preview'])
                ->order(['id' => 'ASC'])
                ->all();

            foreach ($records as $record) {
                $this->out(sprintf('Saving %s %d', lcfirst(Inflector::singularize($tableName)), $record->get('id')));
                $record->set('preview', '');
                $table->saveOrFail($record);
            }
        }
    }

    /**
     * Gets the option parser instance and configures it.
     * @return ConsoleOptionParser
     * @uses MeTools\Shell\InstallShell::getOptionParser()
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        foreach (get_child_methods($this) as $subcommand) {
            $parser->addSubcommand($subcommand, ['help' => Inflector::humanize(Inflector::underscore($subcommand))]);
        }

        return $parser;
    }
}
