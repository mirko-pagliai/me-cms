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

/**
 * Sets the height for the container elements.
 * Specifically, it sets the height of the content and of the sidebar.
 */
function setContainerHeight() {
	//Gets the maximum height available
	var availableHeight = getAvailableHeight();
	
	//The content has the maximum height available
	$('#content').css('min-height', availableHeight);
	
	//The sidebar height is the maximum available height or the content height, if this is greater
	$('#sidebar').css('min-height', availableHeight > $('#content').height() ? availableHeight : $('#content').height());
}

/**
 * Sets the height for the KCFinder i frame.
 */
function setKcfinderHeight() {
	if(!$('#kcfinder').length)
		return;
		
	//For now, the maximum height is the maximum height available
	var maxHeight = getAvailableHeight();
	
	//Subtracts content padding
	maxHeight -= parseInt($('#content').css('padding-top')) + parseInt($('#content').css('padding-bottom'));
	
	//Subtracts the height of each child element of content
	$('#content > * > *:not(#kcfinder)').each(function() {
		maxHeight -= $(this).outerHeight(true);
	});
		
	$('#kcfinder').height(maxHeight);
}

//On windows load and resize
$(window).on('load resize', function() {
	//Sets the height for the container elements (content and sidebar)
	setContainerHeight();
});

$(function() {
	//Sets the height for the KCFinder iframe
	setKcfinderHeight();
	
	//Adds the "data-parent" attribute to the collapsed sidebar
	$('#sidebar a').attr('data-parent', '#sidebar');
	
	//Gets the sidebar position
	var sidebarPosition = $('#sidebar').position();
	
	//Sidebar affix
	$('#sidebar').affix({
		offset: { top: sidebarPosition.top }
	})
	
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