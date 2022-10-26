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
 * Base class for unit tests for block_rss_thumbnails/rss_collector/XML_parser.
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails;

use advanced_testcase;
use block_rss_thumbnails\feed_creator;
use block_rss_thumbnails\output\feed;
use block_rss_thumbnails\output\item;

/**
 * Unit tests for the xml parser of block_rss_thumbnails.
 *
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed_creator_test extends advanced_testcase {

    /**
     * Tests that the {@see feed_creator::create_feed()} method works fine
     *
     * @return void
     * @covers \block_rss_thumbnails\feed_creator::create_feed
     */
    public function test_create_feed() {
        global $CFG;

        $maxentries = 5;
        $feed = feed_creator::create_feed_from_url(
                $CFG->dirroot . '/blocks/rss_thumbnails/tests/fixtures/sample-feed.xml',
                $maxentries
        );
        self::assertEquals("IMT", $feed->get_title());
        self::assertEquals($maxentries, count($feed->get_items()));
        self::assertEquals("https://www.imt.fr/imt-accueil/", $feed->get_link());
    }

    /**
     * Tests that the {@see feed_creator::create_feed()} method returns null if an invalid xml file is given
     *
     * @return void
     * @covers \block_rss_thumbnails\feed_creator::create_feed
     */
    public function test_create_feed_returns_null_if_invalid_xml() {
        global $CFG;

        $feed = feed_creator::create_feed(
            file_get_contents($CFG->dirroot . '/blocks/rss_thumbnails/tests/fixtures/sample-item.xml'),
            5
        );
        self::assertNull($feed);
    }

    /**
     * Tests that the {@see feed_creator::create_item()} method works fine
     *
     * @return void
     * @throws moodle_exception
     * @covers \block_rss_thumbnails\feed_creator::create_item
     */
    public function test_create_item() {
        global $CFG;

        $item = feed_creator::create_item(
                simplexml_load_file($CFG->dirroot . '/blocks/rss_thumbnails/tests/fixtures/sample-item.xml')->item
        );
        self::assertEquals(item::format_title("La Chaire « Valeurs et Politiques des Informations Personnelles »".
            " lance un cycle de conférences sur les identités numériques de confiance"), $item->get_title());
        self::assertEquals("La Chaire « Valeurs et Politiques des Informations Personnelles » de l’Sample org".
            " (SAMPLE) décrypte la notion d’identité numérique. Les identités numériques, qu’elles soient notamment".
            " régaliennes ou personnelles, constituent une question sociétale majeure. Leurs usages et leur régulation".
            " évoluent rapidement à l’aune de la métamorphose numérique. Dans cette perspective, la Chaire VP-IP organise".
            " un cycle de conférences [&#8230;]", $item->get_description());
        self::assertEquals(strtotime("Wed, 20 Jan 2021 15:18:38 +0000"), $item->get_timestamp());
        self::assertEquals("https://www.sample.fr/la-chaire-valeurs-et-politiques-des-informations-personnelles".
            "-lance-un-cycle-de-conferences-sur-les-identites-numeriques-de-confiance/", $item->get_link());
        self::assertEquals(1, count($item->get_categories()));
        self::assertEquals(
                "https://www.sample.fr/wp-content/uploads/2021/01/face-detection-4760361_640-80x80.jpg",
                $item->get_imageurl()
        );
    }

}
