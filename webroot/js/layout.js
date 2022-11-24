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
