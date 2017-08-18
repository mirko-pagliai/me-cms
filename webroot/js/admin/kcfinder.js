/*!
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
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