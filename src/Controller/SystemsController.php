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
namespace MeCms\Controller;

use Cake\Filesystem\File;
use Cake\I18n\Time;
use MeCms\Controller\AppController;

/**
 * Systems controller
 */
class SystemsController extends AppController {
	/**
	 * Accept cookies policy.
	 * It sets the cookie to remember the user accepted the cookie policy and redirects
	 */
	public function accept_cookies() {
		//Sets the cookie
		$this->Cookie->config(['expires' => '+999 days'])->write('cookies-policy', TRUE);
		
		return $this->redirect($this->referer('/', TRUE));
	}
	
	/**
	 * Contact form
	 * @see MeCms\Form\ContactForm
	 * @see MeCms\Form\ContactForm::execute()
	 * @uses MeTools\Controller\Component\Recaptcha::check()
	 * @uses MeTools\Controller\Component\Recaptcha::getError()
	 */
	public function contact_form() {
		//Checks if the contact form is enabled
		if(!config('frontend.contact_form')) {
			$this->Session->Error(__d('me_cms', 'Disabled'));
			return $this->redirect(['_name' => 'homepage']);
		}
		
		$contact = new \MeCms\Form\ContactForm();
		
		if($this->request->is('post')) {
			//Checks for reCAPTCHA, if requested
			if(config('security.recaptcha') && !$this->Recaptcha->check()) {
				$this->Flash->error($this->Recaptcha->getError());
			}
			else {
				//Sends the email
				if($contact->execute($this->request->data)) {
					$this->Flash->success(__d('me_cms', 'The email has been sent'));
					return $this->redirect(['_name' => 'homepage']);
				} 
				else
					$this->Flash->error(__d('me_cms', 'The email was not sent'));
			}
        }
		
		$this->set(compact('contact'));
	}
	
	/**
	 * "Ip not allowed" page
	 * @uses MeCms\Controller\AppController::isBanned()
	 */
	public function ip_not_allowed() {
		//If the user's IP address is not banned
		if(!$this->isBanned())
			return $this->redirect(['_name' => 'homepage']);
		
		$this->viewBuilder()->layout('login');
	}
	
	/**
	 * Offline page
	 */
	public function offline() {
		//If the site has not been taken offline
		if(!config('frontend.offline'))
			return $this->redirect(['_name' => 'homepage']);
		
		$this->viewBuilder()->layout('login');
	}
    
    /**
     * Internal method to generate and write the sitemap
     * @return string Sitemap content
     * @uses MeCms\Utility\Sitemap::generate;
     */
    protected function _sitemap() {
        $sitemap = \MeCms\Utility\Sitemap::generate();

        (new File(SITEMAP, TRUE, 0777))->write($sitemap);

        return $sitemap;
    }

    /**
     * Returns the site sitemap.
     * If the sitemap doesn't exist or has expired, it generates and writes the sitemap.
     * @uses _sitemap()
     */
    public function sitemap() {
        //If the sitemap doesn't exists, it writes the sitemap
        if(!is_readable(SITEMAP)) {
            $sitemap = $this->_sitemap();
        }
        else {
            $time = Time::createFromTimestamp(filemtime(SITEMAP));
            
            //If the sitemap has expired, it writes a new sitemap
            if($time->modify('+6 hours')->isPast()) {
                $sitemap = $this->_sitemap();
            }
            else {
                $sitemap = file_get_contents(SITEMAP);
            }
        }

        $this->response->body($sitemap);
        $this->response->type('xml');
        
        return $this->response;
    }
}