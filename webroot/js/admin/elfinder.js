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
 * Sets the iframe minimum height.
 */
function setIframeHeight()
{
    var content = $("#content");
    var iframe = $("#file-explorer");

    if (!iframe.length) {
        return;
    }

    //For now, the minimum height is the maximum height available
    var minHeight = getAvailableHeight();

    //Subtracts padding, border, and margin of #content
    minHeight -= (content.outerHeight(true) - content.height());

    //Subtracts the height of each child element of content
    iframe.siblings().each(function () {
        minHeight -= $(this).outerHeight(true);
    });

    iframe.css("minHeight", minHeight - 5);
}

//On windows load and resize, it sets the iframe minimum height
$(window).on("load resize", function () {
    setIframeHeight();
});