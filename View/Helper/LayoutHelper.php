<?php
/**
 * LayoutHelper.
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Helper
 */

/**
 * Layout Helper.
 * 
 * This helper allows you to gerate the layouts.
 */
class LayoutHelper extends AppHelper {
    /**
     * Helpers
     * @var array
     */
    public $helpers = array('Html' => array('className' => 'MeTools.MeHtml'));
	
	/**
     * Adds a css file to the layout.
	 * 
	 * It uses the first argument if debugging is disabled, otherwise the second argument if debugging is enabled.
	 * @param mixed $path Css filename or an array of css filenames. This is to be used when debugging is disabled
	 * @param mixed $pathForDebug Css filename or an array of css filenames. This is to be used when debugging is enabled
	 * @param array $options Array of options and HTML attributes
     * @return string Html, `<link>` or `<style>` tag
	 * @uses MeHtmlHelper:css()
	 */
	public function css($path, $pathForDebug, $options = array()) {
		return $this->Html->css(Configure::read('debug') ? $pathForDebug : $path, am(array('inline' => TRUE), $options));
	}
	
    /**
     * Alias for `script()` method
     * @see script()
     */
    public function js() {
        return call_user_func_array(array(get_class(), 'script'), func_get_args());
    }
	
	/**
     * Adds a js file to the layout.
	 * 
	 * It uses the first argument if debugging is disabled, otherwise the second argument if debugging is enabled.
	 * @param mixed $url Javascript files as string or array. This is to be used when debugging is disabled
	 * @param mixed $urlForDebug Javascript files as string or array. This is to be used when debugging is enabled
	 * @param array $options Array of options and HTML attributes
     * @return mixed String of `<script />` tags or NULL if `$inline` is FALSE or if `$once` is TRUE
	 * and the file has been included before
	 * @uses MeHtmlHelper:script()
	 */
    public function script($url, $urlForDebug, $options = array()) {
		return $this->Html->script(Configure::read('debug') ? $urlForDebug : $url, am(array('inline' => TRUE), $options));
	}
}