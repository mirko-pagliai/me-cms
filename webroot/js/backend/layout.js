/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 */

/**
 * Resizes the sidebar
 */
function resizeSidebar() {
	//Gets the windows height
	var windowHeight = $(window).height()-$('#topbar').innerHeight();
	//Gets the content height
	var contentHeight = $('#content').innerHeight();
	
	//The sidebar height will be the greater of the two heights
	$('#sidebar').css('minHeight', windowHeight > contentHeight ? windowHeight : contentHeight);
}

$(function() {
	//Resize the sidebar on windows load and on window resize
	$(window).on('load resize', function () {
		resizeSidebar();
	});
	
	//Adds the "data-parent" attribute for collapsed sidebar
	$('#sidebar a').attr('data-parent', '#sidebar');
});