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
namespace MeCms\Form;

use Cake\Form\Form;
use Cake\Core\Configure;

/**
 * ContactForm class.
 * 
 * It is used by `MeCms\Controller\SystemsController::contact_form()`.
 */
class ContactForm extends Form {
    /**
	 * Defines the validator using the methods on Cake\Validation\Validator or 
	 * loads a pre-defined validator from a concrete class.
	 * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\AppValidator
	 */
    protected function _buildValidator(\Cake\Validation\Validator $validator) {
		$validator = new \MeCms\Model\Validation\AppValidator();
				
		//First name
		$validator->requirePresence('first_name');
		
		//Last name
		$validator->requirePresence('last_name');
		
		//Email
		$validator->requirePresence('email');
		
		//Message
		$validator->requirePresence('message')
			->add('message', ['lengthBetween' => [
				'message'	=> __d('me_cms', 'Must be between {0} and {1} chars', 10, 1000),
				'rule'		=> ['lengthBetween', 10, 1000]
			]]);
		
        return $validator;
    }

	/**
	 * Used by `execute()` to execute the form's action
	 * @param array $data Form data
	 * @return boolean
	 * @uses MeCms\Network\Email\Email
	 */
    protected function _execute(array $data) {
		return (new \MeCms\Network\Email\Email)->from([$data['email'] => sprintf('%s %s', $data['first_name'], $data['last_name'])])
			->to(config('email.webmaster'))
			->subject(__d('me_cms', 'Email from {0}', config('main.title')))
			->template('MeCms.Systems/contact_form')
			->set($data)
			->send();
    }
}