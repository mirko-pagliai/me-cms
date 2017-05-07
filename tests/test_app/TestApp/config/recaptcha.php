<?php
/**
 * Before using reCAPTCHA, you have to get keys:
 * https://www.google.com/recaptcha
 *
 * When you have keys, set them below and RENAME THIS FILE in "recaptcha.php".
 * Remember: keys for forms and keys for emails ARE DIFFERENT.
 */

return ['Recaptcha' => [
    /**
     * Form keys
     * @see https://www.google.com/recaptcha/admin
     */
    'Form' => [
        //Form public key
        'public' => '0000000000000000000000000000000',
        //Form private key
        'private' => '0000000000000000000000000000000',
    ],
    /**
     * Mail keys
     * @see http://www.google.com/recaptcha/mailhide/apikey
     */
    'Mail' => [
        //Mail public key
        'public' => '0000000000000000000000000000000',
        //Mail private key
        'private' => '0000000000000000000000000000000',
    ],
]];
