<?php
//Widgets. You can use the plugin notation (eg., `PluginName.widgetName`)
return ['Widgets' => [
    //General widgets
    'general' => [
        'MeCms.Pages::categories' => ['render' => 'form'],
        'MeCms.Pages::pages',
        //With `me-cms-photos` plugin:
        //
        //'MeCms/Photos.Photos::albums' => ['render' => 'form'],
        //'MeCms/Photos.Photos::latest' => ['limit' => 1],
        //'MeCms/Photos.Photos::random' => ['limit' => 1],
        'MeCms.Posts::categories' => ['render' => 'form'],
        'MeCms.Posts::latest' => ['limit' => 10],
        'MeCms.Posts::months' => ['render' => 'form'],
        'MeCms.Posts::search',
        'MeCms.PostsTags::popular' => [
            'limit' => 10,
            'prefix' => '#',
            'render' => 'cloud',
            'shuffle' => true,
            'style' => ['maxFont' => 40, 'minFont' => 12],
        ],
    ],
    //Specific widgets for the homepage.
    //If empty, will be used the default widget
    'homepage' => [],
]];
