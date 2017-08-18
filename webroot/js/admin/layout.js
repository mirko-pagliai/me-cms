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
    return $(window).height() - $('#topbar').outerHeight(true);
}

/**
 * Toggles the filter form
 */
function toggleFilterForm()
{
    var form = $('.filter-form legend');

    $('.fa', form).toggleClass('fa-eye fa-eye-slash');
    $(form).nextAll().toggle();
}

//On windows load and resize, it sets the maximum height available for the content
$(window).on('load resize', function () {
    $('#content').css('min-height', getAvailableHeight());
});

$(function () {
    //Adds the "data-parent" attribute to all "collapse" wrapper of the sidebar
    $('#sidebar:visible .collapse').attr('data-parent', '#sidebar-accordion');

    //Checks if there is the cookie of the last open menu
    if (Cookies.get('sidebar-lastmenu') && $('#sidebar').is(':visible')) {
        //Gets the element (menu) ID
        var id = '#' + Cookies.get('sidebar-lastmenu');

        //Opens the menu
        $(id, '#sidebar').addClass('show').prev('a').removeClass('collapsed').attr('aria-expanded', 'true');
    }

    //On click on a sidebar menu
    $('#sidebar a[data-toggle=collapse]').click(function () {
        //Saves the menu ID into a cookie
        Cookies.set('sidebar-lastmenu', $(this).attr('aria-controls'), { path: '' });
    });

    //Gets query string as objects, removing empty values and pagination values
    var queryString = $.map(document.location.search.replace(/(^\?)/, '').split('&'), function (value, key) {
        value = value.split('=');

        if (value[0] == 'direction' || value[0] == 'page' || value[0] == 'render' || value[0] == 'sort') {
            return null;
        }

        if (value[1] == "" || value[1] == null || value[1] == undefined) {
            return null;
        }

        var obj = {};
        obj[value[0]] = value[1];

        return obj;
    });

    //If there are no query string values, toggles the filter form
    if (!Object.keys(queryString).length && $('.filter-form legend').length) {
        toggleFilterForm();
    }

    //On click on legend of a filter form, toggles the filter form
    $('.filter-form legend').click(function () {
        toggleFilterForm();
    });
});