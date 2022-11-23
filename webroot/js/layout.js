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
 * Sets the footer class.
 *
 * It sets the footer to `fixed` position when needed, that is when the
 *  document body is lower than the window height.
 */
function setFooterClass()
{
    if (!$("footer").length) {
        return;
    }

    //If there's a difference between the windows height and the body height,
    //  applies the `fixed` class
    if ($(window).height() - $("body").height() > 0) {
        $("footer").addClass("fixed-bottom");
    } else {
        $("footer").removeClass("fixed-bottom");
    }
}

//On windows load and resize, it sets the footer class
$(window).on("load resize", function () {
    setFooterClass();
});

$(function () {
    //On click on the "accept" button for cookies policy
    $("#cookies-policy-accept").click(function (event) {
        event.preventDefault();

        //Removes the cookies policy alert
        $("#cookies-policy").remove();

        //Sets the cookies
        Cookies.set("cookies-policy", true, {
            expires: 999, path: "/"
        });
    });
});
