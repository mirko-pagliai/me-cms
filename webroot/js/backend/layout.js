/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 */

/**
 * Gets the maximum height available.
 * The maximum available height is equal to the window height minus the topbar height.
 */
function getAvailableHeight() {
	return $(window).height() - $('#topbar').outerHeight(true);
}

//On windows load and resize, it sets the maximum height available for the content
$(window).on('load resize', function() {
	$('#content').css('min-height', getAvailableHeight());
});

$(function() {
	//Adds the "data-parent" attribute to all links of the sidebar
	$('#sidebar a').attr('data-parent', '#sidebar');
		
	//Sidebar affix
	$('#sidebar').affix({ offset: { top: $('#sidebar').position().top }});
	
	//Checks if there is the cookie of the last open menu
	if($.cookie('sidebar-lastmenu')) {
		//Gets the element (menu) ID
		var id = '#' + $.cookie('sidebar-lastmenu');
		
		//Opens the menu
		$(id, '#sidebar').addClass('collapse in').attr('aria-expanded', 'true').prev('a').removeClass('collapsed').attr('aria-expanded', 'true');
	}
	
	//On click on a sidebar menu
	$('#sidebar a[data-toggle=collapse]').click(function() {		
		//Saves the menu ID into a cookie
		$.cookie('sidebar-lastmenu', $(this).next().attr('id'), { path: '/' });
	});
});