/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 */

$(function() {
	//Hides attributes, trace and stack trace wrappers
	$('.log-attributes, .log-trace, .log-stack-trace').hide();
	
	//Toggles the log attributes, on click
	$('.toggle-log-attributes').click(function(event) {
		event.preventDefault();
		
		$(this).parent().nextAll('.log-attributes').toggle();
	});
	
	//Toggles the log trace, on click
	$('.toggle-log-trace').click(function(event) {
		event.preventDefault();
		
		$(this).parent().nextAll('.log-trace').toggle();
	});
	
	//Toggles the log stack trace, on click
	$('.toggle-log-stack-trace').click(function(event) {
		event.preventDefault();
		
		$(this).parent().nextAll('.log-stack-trace').toggle();
	});
});