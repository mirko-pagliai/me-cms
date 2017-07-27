<?php
/**
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
use Cake\I18n\I18n;

$this->set([
    'documentData' => [
        'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
    ],
    'channelData' => [
        'title' => __d('me_cms', 'Latest posts'),
        'link' => $this->Url->build('/', true),
        'description' => __d('me_cms', 'Latest posts'),
        'language' => I18n::locale(),
    ],
]);

foreach ($posts as $post) {
    //Sets link
    $link = ['_name' => 'post', $post->slug];

    //Executes BBCode on the text
    $text = $this->BBCode->parser($post->text);

    //Truncates the text if the "<!-- read-more -->" tag is present
    $strpos = strpos($text, '<!-- read-more -->');
    if ($strpos) {
        $text = $this->Text->truncate($text, $strpos, ['exact' => true, 'html' => false]);
    //Truncates the text if requested by the configuration
    } elseif (getConfig('default.truncate_to')) {
        $text = $this->Text->truncate($text, getConfig('default.truncate_to'), ['exact' => false, 'html' => true]);
    }

    //Strips tags
    $text = strip_tags($text);

    //Adds the preview image
    if ($post->preview) {
        $text = $this->Thumb->resize($post->preview['preview'], ['width' => 200]) . '< br/>' . $text;
    }

    echo $this->Rss->item([], [
        'description' => $text,
        'guid' => ['url' => $link, 'isPermaLink' => 'true'],
        'link' => $link,
        'pubDate' => $post->created,
        'title' => $post->title,
    ]);
}
