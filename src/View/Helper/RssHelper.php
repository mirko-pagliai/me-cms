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
namespace MeCms\View\Helper;

use Cake\Utility\Xml;
use Cake\View\Helper;

/**
 * RSS Helper class for easy output RSS structures.
 *
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\TimeHelper $Time
 * @link https://book.cakephp.org/3.0/en/views/helpers/rss.html
 * @deprecated 3.5.0 RssHelper is deprecated and will be removed in 4.0.0
 */
class RssHelper extends Helper
{
    /**
     * Helpers used by RSS Helper
     *
     * @var array
     */
    public $helpers = ['Url', 'Time'];

    /**
     * Base URL
     *
     * @var string
     */
    public $base;

    /**
     * URL to current action.
     *
     * @var string
     */
    public $here;

    /**
     * Parameter array.
     *
     * @var array
     */
    public $params = [];

    /**
     * Current action.
     *
     * @var string
     */
    public $action;

    /**
     * POSTed model data
     *
     * @var array
     */
    public $data;

    /**
     * Name of the current model
     *
     * @var string
     */
    public $model;

    /**
     * Name of the current field
     *
     * @var string
     */
    public $field;

    /**
     * Default spec version of generated RSS
     *
     * @var string
     */
    public $version = '2.0';

    /**
     * Returns an RSS document wrapped in `<rss />` tags
     *
     * @param array $attrib `<rss />` tag attributes
     * @param string|null $content Tag content.
     * @return string An RSS document
     */
    public function document($attrib = [], $content = null)
    {
        if (!isset($attrib['version']) || empty($attrib['version'])) {
            $attrib['version'] = $this->version;
        }

        return $this->elem('rss', $attrib, $content);
    }

    /**
     * Returns an RSS `<channel />` element
     *
     * @param array $attrib `<channel />` tag attributes
     * @param array $elements Named array elements which are converted to tags
     * @param string|null $content Content (`<item />`'s belonging to this channel
     * @return string An RSS `<channel />`
     */
    public function channel($attrib = [], $elements = [], $content = null)
    {
        $elements['link'] = $this->Url->build($elements['link'], ['fullBase' => true]);

        $elems = '';
        foreach ($elements as $elem => $data) {
            $attributes = [];
            $elems .= $this->elem($elem, $attributes, $data);
        }

        return $this->elem('channel', $attrib, $elems . $content, !($content === null));
    }

    /**
     * Generates an XML element
     *
     * @param string $name The name of the XML element
     * @param array $attrib The attributes of the XML element
     * @param string|array|null $content XML element content
     * @param bool $endTag Whether the end tag of the element should be printed
     * @return string XML
     */
    public function elem($name, $attrib = [], $content = null, $endTag = true)
    {
        $xml = '<' . $name;
        $bareName = $name;
        $xml .= '>' . $content . '</' . $name . '>';
        $elem = Xml::build($xml, ['return' => 'domdocument']);
        $nodes = $elem->getElementsByTagName($bareName);
        if ($attrib) {
            foreach ($attrib as $key => $value) {
                $nodes->item(0)->setAttribute($key, $value);
            }
        }

        $xml = $elem->saveXml();
        $xml = trim(substr($xml, strpos($xml, '?>') + 2));

        return $xml;
    }

    /**
     * Event listeners.
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [];
    }
}
