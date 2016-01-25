/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 */

$(function() {
	//On click on the "accept" button for cookies policy
	$('#cookies-policy-accept').click(function(event) {
		event.preventDefault();
		
		//Removes the cookies policy alert
		$('#cookies-policy').remove();
		
		//Sets the cookies
		$.cookie('cookies-policy', true, { path: '/' });
	});
});