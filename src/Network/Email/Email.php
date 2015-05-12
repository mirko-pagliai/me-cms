<?php
/**
 * This file is part of MeTools.
 *
 * MeTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeTools.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Network\Email;

use MeTools\Network\Email\Email as MeToolsEmail;

/**
 * Email class.
 * 
 * Note that the view variables (`$_viewVars`) are set by `MeTools\View\EmailView`.
 * 
 * Example:
 * <code>
 * use MeCms\Network\Email\Email;
 * 
 * $email = new Email('default');
 * $email->to('you@example.com')
 *		->subject('About')
 *		->send('My message');
 * </code> 
 */
class Email extends MeToolsEmail {
	/**
	 * What format should the email be sent in
	 * @var string 
	 */
	protected $_emailFormat = 'html';
	
	/**
	 * Constructor
	 * @param @param array|string|null $config Array of configs, or string to load configs from email.php
	 * @uses MeTools\Network\Email\Email::__construct()
	 * @uses $_from
	 */
	public function __construct($config = NULL) {
		$this->_from = [config('email.webmaster') => config('main.title')];
		
		parent::__construct(empty($config) ? config('email.config') : $config);
	}
	
	/**
	 * Reset all the internal variables to be able to send out a new email.
	 * @return \MeCms\Network\Email\Email
	 * @uses MeTools\Network\Email\Email::reset()
	 * @uses $_emailFormat
	 * @uses $_from
	 */
	public function reset() {
		parent::reset();
		
		$this->_emailFormat = 'text';
		$this->_from = [];
		
		return $this;
	}
	
	/**
	 * Template and layout
	 * @param bool|string $template Template name or null to not use
	 * @param bool|string $layout Layout name or null to not use
	 * @return array|$this
	 * @uses MeTools\Network\Email\Email::template()
	 * @uses $_layout
	 */
	public function template($template = FALSE, $layout = FALSE) {
		return parent::template($template, empty($layout) ? $this->_layout : $layout);
	}
}