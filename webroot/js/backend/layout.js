/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
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
	$('#sidebar:visible a').attr('data-parent', '#sidebar');
		
	//Sidebar affix
	$('#sidebar:visible').affix({
		offset: {
			top: $('#sidebar').position().top
		}
	});
	
	//Checks if there is the cookie of the last open menu
	if($.cookie('sidebar-lastmenu') && $('#sidebar').is(':visible')) {
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
	
	//On click on legend of a filter form
	$('.filter-form legend').click(function() {
		$('.fa', this).toggleClass('fa-eye fa-eye-slash');
		
		if(window.location.search)
			$('+ div', this).toggle();
		else
			$('+ div', this).slideToggle();
	});
	
	//Gets query string as objects, removing empty values and pagination values
	var queryString = $.map(document.location.search.replace(/(^\?)/, '').split('&'), function(value, key) {
		value = value.split('=');
		
		if(value[0] == 'direction' || value[0] == 'page' || value[0] == 'sort')
			return null;
		
		if(value[1] == "" || value[1] == null || value[1] == undefined)
			return null;
		
		var obj = {};
		obj[value[0]] = value[1];
		return obj;
	});	
	
	//If there are query string values, shows the filters form
	if(Object.keys(queryString).length && $('.filter-form legend').length)
		$('.filter-form legend').trigger('click');
});