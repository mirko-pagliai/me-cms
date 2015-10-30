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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @see			http://api.cakephp.org/3.1/class-Cake.Mailer.Mailer.html Mailer
 */
namespace MeCms\Mailer;

use Cake\Mailer\Mailer as BaseMailer;

/**
 * Mailer classes let you encapsulate related Email logic into a reusable.
 * 
 * Rewrites {@link http://api.cakephp.org/3.1/class-Cake.Mailer.Mailer.html Mailer}.
 */
class Mailer extends BaseMailer {
	/**
	 * Constructor
	 * @param \Cake\Mailer\Email|null $email Email instance
	 * @uses Cake\Mailer\Mailer::__construct()
	 */
	public function __construct(\Cake\Mailer\Email $email = NULL) {
		parent::__construct($email);
		
		$this->_email->profile('default')
			->helpers('MeTools.Html')
			->set('ip_address', get_client_ip())
			->from(config('email.webmaster'), config('main.title'))
			->sender(config('email.webmaster'), config('main.title'))
			->emailFormat('html');
	}
}