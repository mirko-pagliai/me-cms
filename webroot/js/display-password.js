/*!
 * This file is part of MeCms.
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 */

$(function () {
    //On click on button to display passwords
    $('.display-password').click(function (event) {
        event.preventDefault();

        //Gets the icon
        var icon = $(this).children('i.fa');

        //Gets the password field
        var oldField = $(this).closest('.input').find('input');

        //Creates a replace field, setting the same value
        var replaceField = $('<input />').val(oldField.val());

        //Copies each attribute to the replace field
        oldField.each(function () {
            $.each(this.attributes, function () {
                if (this.specified) {
                    replaceField.attr(this.name, this.value);
                }
            });
        });

        //Sets the `type` for the replace field and changes the button icon
        if (oldField.attr('type') === 'password') {
            replaceField.attr('type', 'text');

            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            replaceField.attr('type', 'password');

            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }

        //Inserts the replace field and removes the old field
        replaceField.insertBefore(oldField);
        oldField.remove();
    });
});
