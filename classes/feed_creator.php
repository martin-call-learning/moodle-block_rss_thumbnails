<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains class bock_rss_thumbnails\feed_creator
 *
 * @package   block_rss_thumbnails
 * @copyright 202 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails;

use block_rss_thumbnails\output\channel_image;
use block_rss_thumbnails\output\feed;
use block_rss_thumbnails\output\item;
use moodle_exception;
use moodle_url;
use SimpleXMLElement;

/**
 * Class allowing to create feed objects from RSS feeds as XML files by calling several static methods.
 *
 * @package   block_rss_thumbnails
 * @copyright 202 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed_creator {

    /** A list of XML entities. */
    const XML_ENTITIES = array(
        '&#34;', '&#38;', '&#38;', '&#60;', '&#62;', '&#160;', '&#161;', '&#162;', '&#163;', '&#164;', '&#165;', '&#166;', '&#167;',
        '&#168;', '&#169;', '&#170;', '&#171;', '&#172;', '&#173;', '&#174;', '&#175;', '&#176;', '&#177;', '&#178;', '&#179;',
        '&#180;', '&#181;', '&#182;', '&#183;', '&#184;', '&#185;', '&#186;', '&#187;', '&#188;', '&#189;', '&#190;', '&#191;',
        '&#192;', '&#193;', '&#194;', '&#195;', '&#196;', '&#197;', '&#198;', '&#199;', '&#200;', '&#201;', '&#202;', '&#203;',
        '&#204;', '&#205;', '&#206;', '&#207;', '&#208;', '&#209;', '&#210;', '&#211;', '&#212;', '&#213;', '&#214;', '&#215;',
        '&#216;', '&#217;', '&#218;', '&#219;', '&#220;', '&#221;', '&#222;', '&#223;', '&#224;', '&#225;', '&#226;', '&#227;',
        '&#228;', '&#229;', '&#230;', '&#231;', '&#232;', '&#233;', '&#234;', '&#235;', '&#236;', '&#237;', '&#238;', '&#239;',
        '&#240;', '&#241;', '&#242;', '&#243;', '&#244;', '&#245;', '&#246;', '&#247;', '&#248;', '&#249;', '&#250;', '&#251;',
        '&#252;', '&#253;', '&#254;', '&#255;', '&#338;'
    );

    /** A list of HTML entities. */
    const HTML_ENTITIES = array(
        '&quot;', '&amp;', '&amp;', '&lt;', '&gt;', '&nbsp;', '&iexcl;', '&cent;', '&pound;', '&curren;', '&yen;', '&brvbar;',
        '&sect;', '&uml;', '&copy;', '&ordf;', '&laquo;', '&not;', '&shy;', '&reg;', '&macr;', '&deg;', '&plusmn;', '&sup2;',
        '&sup3;', '&acute;', '&micro;', '&para;', '&middot;', '&cedil;', '&sup1;', '&ordm;', '&raquo;', '&frac14;', '&frac12;',
        '&frac34;', '&iquest;', '&Agrave;', '&Aacute;', '&Acirc;', '&Atilde;', '&Auml;', '&Aring;', '&AElig;', '&Ccedil;',
        '&Egrave;', '&Eacute;', '&Ecirc;', '&Euml;', '&Igrave;', '&Iacute;', '&Icirc;', '&Iuml;', '&ETH;', '&Ntilde;', '&Ograve;',
        '&Oacute;', '&Ocirc;', '&Otilde;', '&Ouml;', '&times;', '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;', '&Uuml;', '&Yacute;',
        '&THORN;', '&szlig;', '&agrave;', '&aacute;', '&acirc;', '&atilde;', '&auml;', '&aring;', '&aelig;', '&ccedil;', '&egrave;',
        '&eacute;', '&ecirc;', '&euml;', '&igrave;', '&iacute;', '&icirc;', '&iuml;', '&eth;', '&ntilde;', '&ograve;', '&oacute;',
        '&ocirc;', '&otilde;', '&ouml;', '&divide;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;', '&uuml;', '&yacute;', '&thorn;',
        '&yuml;', '&oelig'
    );

    /**
     * A function that creates a feed from the given url of an xml file.
     *
     * @param string $xmlurl the url of the file
     * @param int $maxentries the maximum number of items this feed will have
     * @param bool $showtitle should we display the title of the feed ?
     * @return feed|null the feed created or null if an error occurred
     */
    public static function create_feed_from_url(string $xmlurl, int $maxentries, bool $showtitle = true) {
        return self::create_feed(file_get_contents($xmlurl), $maxentries, $showtitle);
    }

    /**
     * A function that creates a feed from the given xml file as a string.
     *
     * Notice that the xml source isn't the path of the xml file. You'll have to use {@see file_get_contents()} first.
     *
     * @param string $xmlsource the xml file content as a string.
     * @param int $maxentries the maximum number of items this feed will have
     * @param bool $showtitle should we display the title of the feed ?
     * @return feed|null the feed created or null if an error occurred.
     */
    public static function create_feed(string $xmlsource, int $maxentries, bool $showtitle = true): ?feed {
        $xmlsource = self::normalize_xml_file($xmlsource);
        $simplexmlelt = simplexml_load_string($xmlsource);

        $channel = $simplexmlelt->channel;
        if (!$channel) {
            return null;
        }

        $image = $channel->image;
        if (!empty($image)) {
            $image = new channel_image(
                    new moodle_url($image->url),
                    $image->title,
                    new moodle_url($image->link)
            );
        } else {
            $image = null;
        }
        $feed = new feed(
                $channel->title,
                new moodle_url($channel->link),
                $image,
                $showtitle,
                $image ? true : false,
        );

        $counter = 0;
        foreach (self::create_items($simplexmlelt) as $item) {
            if ($counter >= $maxentries) {
                break;
            }
            if (empty($item->imageurl->url) && $feed->get_image()) {
                $item->imageurl = $feed->get_image()->get_url();
            }
            $feed->add_item($item);
            $counter++;
        }

        return $feed;
    }

    /**
     * Creates all the items of a feed by calling {@see self::create_item()} and returns them into an array.
     *
     * It has to be a whole xml file loaded by simplexml or you may have errors by calling ->item on $simplexmlelt->channel
     * which could be null if the file is incorrect or if it hasn't been loaded correctly.
     *
     * @param SimpleXMLElement $simplexmlelt the source.
     * @return array the array containing all the feed's items.
     */
    private static function create_items(SimpleXMLElement $simplexmlelt): array {
        $items = [];
        foreach ($simplexmlelt->channel->item as $itemxml) {
            $items[] = self::create_item($itemxml);
        }
        return $items;
    }

    /**
     * Creates an item from an {@see SimpleXMLElement}.
     * <br><br>
     * Please make sure the {@see SimpleXMLElement} contains and only contains one item.<br>
     * (ex : cf rss_thumbnails\tests\fixtures\sample-item.xml)
     * <br><br>
     * First of all the function will start searching for an 'image' tag.
     * If it doesn't exist, it'll search for a 'media' namespace hoping to find an image url.<br>
     * If it still cannot find it, the function will lay {@see item::$imageurl} to null so the feed creator will be able to replace
     * it with the feed's image.
     *
     * @param SimpleXMLElement $xmlitem The source of the item to be created.
     * @return item The item created.
     * @throws moodle_exception See the use of {@see moodle_url}
     */
    public static function create_item(SimpleXMLElement $xmlitem) {
        $categories = [];
        foreach ($xmlitem->category as $category) {
            $categories[] = $category;
        }

        $namespaces = $xmlitem->getNamespaces(true);

        if ($xmlitem->image) {
            $imageurl = new moodle_url($xmlitem->image);
        } else if (array_key_exists("media", $namespaces)) {
            $medianode = $xmlitem->children($namespaces["media"]);
            $imageurl = new moodle_url($medianode->attributes()["url"]);
        } else {
            $imageurl = new moodle_url("");
        }

        $id = $xmlitem->guid ?? $xmlitem->title;
        $link = new moodle_url($xmlitem->link);
        $description = $xmlitem->description;
        $timestamp = strtotime($xmlitem->pubDate);
        $title = $xmlitem->title;

        return new item($id, $link, $description, $timestamp, true, $title, $imageurl, $categories);
    }

    /**
     * Replaces HTML entities that could be in the xml file so SimpleXML will be able to interpret every entity.
     *
     * @param string $xmlfile the source file content as a string.
     * @return string the source file content with only XML entities.
     */
    public static function normalize_xml_file(string $xmlfile): string {
        return str_replace(self::HTML_ENTITIES, self::XML_ENTITIES, $xmlfile);
    }
}
