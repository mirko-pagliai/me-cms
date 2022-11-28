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
 * Gets the maximum height available.
 * The maximum available height is equal to the window height minus the topbar height.
 */
function getAvailableHeight()
{
    return $(window).height() - $("#userbar").outerHeight(true);
}

/**
 * Toggles the filter form
 */
function toggleFilterForm()
{
    var form = $(".filter-form legend");

    $(".fas", form).toggleClass("fa-eye fa-eye-slash");
    $(form).nextAll().toggle();
}

//On windows load and resize, it sets the maximum height available for the content
$(window).on("load resize", function () {
    $("#content").css("min-height", getAvailableHeight());
});

$(function () {
    //Gets query string as objects, removing empty values and pagination values
    var queryString = $.map(document.location.search.replace(/(^\?)/, "").split("&"), function (value) {
        value = value.split("=");

        if (value[0] === "direction" || value[0] === "page" || value[0] === "render" || value[0] === "sort") {
            return null;
        }

        if (value[1] === "" || value[1] === null || value[1] === undefined) {
            return null;
        }

        var obj = {};
        obj[value[0]] = value[1];

        return obj;
    });

    //If there are no query string values, toggles the filter form
    if (!Object.keys(queryString).length && $(".filter-form legend").length) {
        toggleFilterForm();
    }

    //On click on legend of a filter form, toggles the filter form
    $(".filter-form legend").click(function () {
        toggleFilterForm();
    });

    //If the `menuToBeOpen` cookie is present, opens the relative menu
    if (Cookies.get("menuToBeOpen")) {
        $("#accordionSidebar a[data-bs-target='#" + Cookies.get("menuToBeOpen") + "']")[0].click();
    }

    // When opening a menu, save the relative value in the `menuToBeOpen` cookie
    $("#accordionSidebar").on("shown.bs.collapse", function () {
        Cookies.set("menuToBeOpen", $(this).find(".collapse.show").attr("id"));
    });
});
