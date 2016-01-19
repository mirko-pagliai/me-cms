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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Shell;

use Cake\Datasource\ConnectionManager;
use MeTools\Console\Shell;

/**
 * Applies updates
 */
class UpdateShell extends Shell {	
	/**
	 * Updates to 2.1.7 version
	 */
	public function to2v1v7() {
		$this->loadModel('MeCms.Tags');
		
		$connection = ConnectionManager::get('default');
		
		$connection->execute(sprintf('ALTER TABLE `%s` CHANGE `tag` `tag` VARCHAR(30) NOT NULL;', $this->Tags->table()));
	}
	
	/**
	 * Gets the option parser instance and configures it.
	 * @return ConsoleOptionParser
	 * @uses MeTools\Shell\InstallShell::getOptionParser()
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		
		return $parser->addSubcommands([
			'to2v1v7' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.7')]
		]);
	}
}