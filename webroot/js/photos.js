/**
 * Photos js.
 *
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\webroot\js
 */

$(function() {
	/**
	 * When you click on "check/uncheck all", it changes the checkboxed state
	 */
	$('.photos .check-all, .photos .uncheck-all').click(function(event) {
		event.preventDefault();
		
		//Gets inputs
		var inputs = $(this).closest('form').find('input[type=checkbox]');
		
		//If you have clicked on "check all"
		if($(this).hasClass('check-all'))
			inputs.prop('checked', true);
		//Else, if you have clicked on "uncheck all"
		else if ($(this).hasClass('uncheck-all'))
			inputs.prop('checked', false);
	});
});