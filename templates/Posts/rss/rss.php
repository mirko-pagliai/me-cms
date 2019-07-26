<?php
declare(strict_types=1);
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
    'documentData' => ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/'],
    'channelData' => [
        'title' => __d('me_cms', 'Latest posts'),
        'link' => $this->Url->build('/', ['fullBase' => true]),
        'description' => __d('me_cms', 'Latest posts'),
        'language' => I18n::getLocale(),
    ],
]);

foreach ($posts as $post) {
    $link = $this->Url->build(['_name' => 'post', $post->slug], ['fullBase' => true]);

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

    //Strips tags and adds the preview image
    $text = strip_tags($text);
    if (!empty($post->preview[0])) {
        $text = $this->Thumb->resize($post->preview[0]->url, ['width' => 200]) . '<br />' . $text;
    }

    $html = $this->Html->tag('description', htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE));
    $html .= $this->Html->tag('guid', $link, ['isPermaLink' => 'true']);
    $html .= $this->Html->tag('link', $link);
    $html .= $this->Html->tag('pubDate', $this->Time->toRss($post->created));
    $html .= $this->Html->tag('title', strip_tags($post->title));
    echo $this->Html->tag('item', $html);
}
