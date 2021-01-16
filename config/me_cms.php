<?php
return ['MeCms' => [
    //Admin layout
    'admin' => [
        //Number of records to show per page
        'records' => 10,
     ],
    //Default layout
    'default' => [
        //Google Analytics ID or `false` to disable
        'analytics' => false,
        //Displays the alert for the cookie policy
        'cookies_policy' => true,
        //"Contact us" form (enabled or disabled)
        'contact_us' => true,
        //Facebook app ID or `false`
        'facebook_app_id' => false,
        //Uses Fancybox for photos
        'fancybox' => true,
        //Site logo. Relative path to `APP/webroot/img/`
        'logo' => 'logo.png',
        //Site offline (enabled or disabled)
        'offline' => false,
        //Text to display when the site is offline or `false`
        'offline_text' => false,
        //Number of records to show per page
        'records' => 10,
        //Number of records to show on RSS
        'records_for_rss' => 20,
        //Number of records to show for searches
        'records_for_searches' => 20,
        //Adds automatically the meta tag for RSS resources
        'rss_meta' => true,
        //Theme. Must be located into `APP/plugins/`
        'theme' => false,
        //For some mobile browsers you can choose a color for the browser bar.
        //Must be a valid HEX color or `false` to disable.
        //See https://developers.google.com/web/updates/2014/11/Support-for-theme-color-in-Chrome-39-for-Android
        'toolbar_color' => false,
        //Number of characters to truncate a text. `0` or `false` to disable.
        //Note that you can use the "<!-- read-more -->" tag to indicate
        //  manually where to truncate a text
        'truncate_to' => 1000,
    ],
    //Disqus
    'disqus' => [
        //Site shortname or `false` to disable Disqus. To get the site shortname,
        //  you must first create and set up your site on Disqus
        'shortname' => false,
    ],
    //Email
    'email' => [
        //EmailTransport configuration
        'config' => 'default',
        //Address used as the sender for emails sent to users and as a
        //  recipient for the email sent by users
        'webmaster' => 'email@example.com',
    ],
    //Main
    'main' => [
        //Date formats.
        //See: http://php.net/manual/it/datetime.formats.php
        'date' => [
            //Long format
            'long' => 'YYYY/MM/dd',
            //Short format
            'short' => 'yy/MM/dd',
        ],
        //Datetime formats.
        //See: http://php.net/manual/it/datetime.formats.php
        'datetime' => [
            //Long format
            'long' => 'YYYY/MM/dd, HH:mm',
            //Short format
            'short' => 'yy/MM/dd, HH:mm',
        ],
        //Forces debug on localhost (enabled or disabled)
        'debug_on_localhost' => true,
        //Sitemap expiration. Must be a valid strtotime string
        'sitemap_expiration' => '+24 hours',
        //Time formats
        //See: http://php.net/manual/it/datetime.formats.php
        'time' => [
            //Long format
            'long' => 'HH:mm',
            //Short format
            'short' => 'HH:mm',
        ],
        //Site title
        'title' => 'MeCms',
    ],
    //Pages
    'page' => [
        //Displays the page category
        'category' => true,
        //Displays the page created datetime
        'created' => false,
        //This enables or disables the comment system for pages. You will then
        //  be able to choose whether to enable comments for each page
        'enable_comments' => false,
        //Displays the page modified datetime
        'modified' => false,
        //Displays the Shareaholic social buttons.
        //Remember you have to set app and site IDs. See `shareaholic.app_id`
        //  and `shareaholic.site_id`
        'shareaholic' => false,
    ],
    //Posts
    'post' => [
        //Displays the author name as "Posted by"
        'author' => true,
        //Displays the author picture
        'author_picture' => true,
        //Displays the post category
        'category' => true,
        //Displays the post created datetime
        'created' => true,
        //This enables or disables the comment system for posts. You will then
        //  be able to choose whether to enable comments for each post
        'enable_comments' => true,
        //Adds post tags as keywords meta-tag
        'keywords' => true,
        //Displays the post modified datetime
        'modified' => true,
        //Related posts. `false` to disable
        'related' => [
            //Limit of related posts to get for each post.
            //If you use images, it recommended a multiple of 4
            'limit' => 4,
            //Gets only related posts with images
            'images' => true,
        ],
        //Displays the Shareaholic social buttons
        //Remember you have to set app and site IDs. See `shareaholic.app_id`
        //  and `shareaholic.site_id`
        'shareaholic' => false,
        //Displays the post tags
        'tags' => true,
    ],
    //Security
    'security' => [
        //Link for "IP map". The `{IP}` string will be replaced
        'ip_map' => 'https://db-ip.com/{IP}',
        //Link for "IP who is". The `{IP}` string will be replaced
        'ip_whois' => 'https://db-ip.com/{IP}',
        //reCAPTCHA (enabled or disabled).
        //It will be used for some actions, such as signup or password reset
        'recaptcha' => false,
        //Interval between searches, in seconds. `0` or `false` to disable
        'search_interval' => 10,
    ],
    //Shareaholic
    'shareaholic' => [
        //App ID. Used for render the "share buttons" of Shareaholic.
        //You can found it on the "Sharing: Edit Share Button Location"
        'app_id' => '',
        //Site ID. Used for render the "setup code" of Shareaholic.
        //You can found it on the "Site Tools Dashboard"
        'site_id' => '',
    ],
    //Sitemap
    'sitemap' => [
        //Includes pages (pages and categories) in the sitemap
        'pages' => true,
        //Includes posts (posts and categories) in the sitemap
        'posts' => true,
        //Includes tags in the sitemap
        'posts_tags' => true,
        //Includes system pages (eg, the contact form) in the sitemap
        'systems' => true,
        //Includes static pages in the sitemap
        'static_pages' => true,
    ],
    //Users
    'users' => [
        //How to activating accounts:
        //  `0` - No activation required, the account is immediately active;
        //  `1` - The account will be enabled by the user via email (default);
        //  `2` - The account will be enabled by an administrator.
        'activation' => 1,
        //Login with cookies ("remember me" function)
        'cookies_login' => true,
        //Default users group (ID)
        'default_group' => 3,
        //Number of login log per user. `0` or `false` to disable
        'login_log' => 40,
        //Reset password (enabled or disabled)
        'reset_password' => true,
        //Signup (enabled or disabled)
        'signup' => true,
        //Displays the userbar. This will have effect only on the frontend; in
        //  the backend, the userbar will always be visible
        'userbar' => true,
    ],
]];
