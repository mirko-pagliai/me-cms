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
        'public' => 'your-public-key-here',
        //Form private key
        'private' => 'your-private-key-here',
    ],
    /**
     * Mail keys
     * @see http://www.google.com/recaptcha/mailhide/apikey
     */
    'Mail' => [
        //Mail public key
        'public' => 'your-public-key-here',
        //Mail private key
        'private' => 'your-private-key-here',
    ],
]];
