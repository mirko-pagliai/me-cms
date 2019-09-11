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
    //On click on button to display passwords
    $(".display-password").click(function (event) {
        event.preventDefault();

        //Gets the icon
        var icon = $(this).children("i.fas");

        //Gets the password field
        var oldField = $(this).closest(".input").find("input");

        //Creates a replace field, setting the same value
        var replaceField = $("<input />").val(oldField.val());

        //Copies each attribute to the replace field
        oldField.each(function () {
            $.each(this.attributes, function () {
                if (this.specified) {
                    replaceField.attr(this.name, this.value);
                }
            });
        });

        //Sets the `type` for the replace field and changes the button icon
        if (oldField.attr("type") === "password") {
            replaceField.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            replaceField.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }

        //Inserts the replace field and removes the old field
        replaceField.insertBefore(oldField);
        oldField.remove();
    });
});
