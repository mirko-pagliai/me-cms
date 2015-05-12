/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 */
$(function() {
	/**
	 * When adding photos, on click on "check/uncheck all", it changes the checkboxed state
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