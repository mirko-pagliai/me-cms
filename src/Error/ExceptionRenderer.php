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
 * @see			http://api.cakephp.org/3.1/class-Cake.Error.ExceptionRenderer.html ExceptionRenderer
 */
namespace MeCms\Error;

use Cake\Error\ExceptionRenderer as BaseExceptionRenderer;

/**
 * Exception Renderer.
 * Captures and handles all unhandled exceptions. Displays helpful framework errors when debug is true. 
 * When debug is false a CakeException will render 404 or 500 errors. 
 * If an uncaught exception is thrown and it is a type that ExceptionHandler does not know about it will be treated as a 500 error.
 * 
 * Rewrites {@link http://api.cakephp.org/3.1/class-Cake.Error.ExceptionRenderer.html ExceptionRenderer}.
 */
class ExceptionRenderer extends BaseExceptionRenderer {
	/**
	 * Generate the response using the controller object
	 * @param string $template The template to render
	 * @return Cake\Network\Response A response object that can be sent
	 * @uses Cake\Error\ExceptionRenderer::_outputMessage()
	 */
	protected function _outputMessage($template) {
		list($plugin, $template) = pluginSplit($template);
		
		//Sets template from MeCms
		if(empty($plugin))
			$template = sprintf('MeCms.%s', $template);
				
		return parent::_outputMessage($template);
	}
	
	/**
	 * A safer way to render error messages, replaces all helpers, with basics and doesn't call component methods
	 * @param string $template The template to render
	 * @return Cake\Network\Response A response object that can be sent
	 * @uses Cake\Error\ExceptionRenderer::_outputMessageSafe()
	 */
	protected function _outputMessageSafe($template) {
		list($plugin, $template) = pluginSplit($template);
		
		//Sets template from MeCms
		if(empty($plugin))
			$template = sprintf('MeCms.%s', $template);
		
		return parent::_outputMessageSafe($template);
	}
	
	/**
	 * Renders the response for the exception
	 * @return Cake\Network\Response The response to be sent
	 * @uses Cake\Error\ExceptionRenderer::render()
	 */
	public function render() {
		//Sets layout from MeCms
		$this->controller->viewBuilder()->layout('MeCms.error');
			
		return parent::render();
	}
}