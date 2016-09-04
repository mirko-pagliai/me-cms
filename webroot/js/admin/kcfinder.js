/*!
 * This file is part of MeCms.
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 */

/**
 * Sets the height for the KCFinder i frame.
 */
function setKcfinderHeight()
{
    if (!$('#kcfinder').length) {
        return;
    }

    //For now, the maximum height is the maximum height available
    var maxHeight = getAvailableHeight();

    //Subtracts content padding
    maxHeight -= parseInt($('#content').css('padding-top')) + parseInt($('#content').css('padding-bottom'));

    //Subtracts the height of each child element of content
    $('#content > * > *:not(#kcfinder)').each(function () {
        maxHeight -= $(this).outerHeight(true);
    });

    $('#kcfinder').height(maxHeight);
}

//On windows load and resize, it sets the height for the KCFinder iframe
$(window).on('load resize', function () {
    setKcfinderHeight();
});