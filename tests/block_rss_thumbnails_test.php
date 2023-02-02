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
 * Base class for unit tests for block_rss_thumbnails.
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_rss_thumbnails;

use advanced_testcase;
use block_base;
use block_rss_thumbnails\output\item;
use context_system;
use moodle_page;
use moodle_simplepie;
use stdClass;

/**
 * Unit tests for block_rss_thumbnails.
 *
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rss_thumbnails_test extends advanced_testcase {

    /**
     * Expected config data.
     */
    const EXPECTED_CONFIG = '[{"imageurl":"http:\/\/mysite.fr\/wp-content\/uploads\/2020\/11\/DSC_0009-2-480x519.jpg",'
    . '"linkurl":"http:\/\/mysite.fr\/la-sante-globale-fil-conducteur-de-la-strategie-detablissement\/",'
    . '"categories":[{"text":"One Health"}],"date":"1604419500","title":"La sant\u00e9 globale, fil conducteur de la strat\u00e9gie'
    . ' d\u2019\u00e9tablissement"},{"imageurl":"http:\/\/mysite.fr\/wp-content\/uploads\/2020\/10\/Rentree-VETO-2020-1024x504.jpg"'
    . ',"linkurl":"http:\/\/mysite.fr\/les-chiffres-cles-de-la-rentree-2020-de-vetagro-sup\/","categories":[{"text":"Etudiants"}'
    . ',{"text":"Formations"}],"date":"1603116642","title":"Les chiffres cl\u00e9s de la rentr\u00e9e 2020 de VetAgro Sup"}]';

    /**
     * @var block_base|false|null Current block
     */
    protected $block = null;
    /**
     * @var stdClass|null Current user
     */
    protected $user = null;

    /** @var array|object Feed record */
    private $record;

    /**
     * Basic setup for these tests.
     */
    public function setUp(): void {
        global $DB, $CFG;
        $this->resetAfterTest();

        // Insert a new RSS Feed.
        require_once("$CFG->libdir/simplepie/moodle_simplepie.php");

        // A record that has failed before.
        $this->record = (object) [
            'userid' => 1,
            'title' => 'Skip test feed',
            'preferredtitle' => '',
            'description' => 'A feed to test the skip time.',
            'shared' => 0,
            'url' => 'https://example.com/rss',
            'skiptime' => 0,
            'skipuntil' => 0,
        ];
        $this->record->id = $DB->insert_record('block_rss_client', $this->record);

        // Run the scheduled task and have it fail.
        $task = $this->getMockBuilder(refreshfeeds::class)
            ->setMethods(['fetch_feed'])
            ->getMock();

        $piemock = $this->getMockBuilder(moodle_simplepie::class)
            ->setMethods(['error'])
            ->getMock();

        $piemock->method('error')
            ->willReturn(true);

        $task->method('fetch_feed')
            ->willReturn($piemock);

        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('frontpage');
        $blockname = 'rss_thumbnails';
        $page->blocks->load_blocks();
        $page->blocks->add_block_at_end_of_default_region($blockname);
        // Here we need to work around the block API. In order to get 'get_blocks_for_region' to work,
        // we would need to reload the blocks (as it has been added to the DB but is not
        // taken into account in the block manager).
        // The only way to do it is to recreate a page so it will reload all the block.
        // It is a main flaw in the  API (not being able to use load_blocks twice).
        // Alternatively if birecordsbyregion was nullable,
        // should for example have a load_block + create_all_block_instances and
        // should be able to access to the block.
        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('frontpage');
        $page->blocks->load_blocks();
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);
        $block = block_instance($blockname, $block->instance);
        $configdata = ['display_description' => true];
        $block->instance_config_save((object) $configdata);
        $this->block = $block;
        $block = block_instance_by_id($this->block->instance->id);
        $this->block = $block;
    }

    /**
     * Tests if the feed returned by {@see \block_rss_thumbnails::get_feed()} method is valid.
     *
     * @return void
     * @covers \block_rss_thumbnails::get_feeds
     */
    public function test_get_feed() {
        global $CFG;

        $rssfeed = (object) [
            'skipuntil' => 0,
            'url' => $CFG->dirroot . '/blocks/rss_thumbnails/tests/fixtures/sample-feed.xml'
        ];

        $feed = $this->block->get_feed($rssfeed, 5, true);
        $exporteddata = $feed->export_for_template(
            $this->block->page->get_renderer('block_rss_thumbnails')
        );
        $this->assertEquals("IMT", $exporteddata['title']);
        $this->assertNull($exporteddata['image']);
    }

    /**
     * Tests if the items of the feed returned by the {@see \block_rss_thumbnails::get_feed()} method are valid.
     *
     * @return void
     * @covers \block_rss_thumbnails::get_feed
     */
    public function test_get_feed_items() {
        global $CFG;
        $rssfeed = (object) [
            'skipuntil' => 0,
            'url' => $CFG->dirroot . '/blocks/rss_thumbnails/tests/fixtures/sample-feed.xml'
        ];

        $feed = $this->block->get_feed($rssfeed, 5, true);
        $exporteddata = $feed->export_for_template(
            $this->block->page->get_renderer('block_rss_thumbnails')
        );
        $expecteditems = self::get_expected_items();
        $items = $exporteddata["items"];
        $expecteditemslength = count($expecteditems);
        for ($index = 0; $index < $expecteditemslength; $index++) {
            self::assertEquals($expecteditems[$index], $items[$index]);
        }

    }

    /**
     * Get expected items to be returned by the get_feed.
     *
     * @return array[]
     */
    private function get_expected_items() {
        return [
            [
                "id" => "https://www.imt.fr/?p=89572",
                "timestamp" => strtotime("Wed, 19 Oct 2022 13:57:11 +0000"),
                "link" => "https://www.imt.fr/odyssea-les-personnels-de-linstitut-mines-telecom-mobilises-pour".
                    "-la-lutte-contre-le-cancer-du-sein/",
                "imageurl" => "https://www.imt.fr/wp-content/uploads/2022/10/Odyssea2022_montage-groupes-80x80.png",
                "categories" => [
                    "À la une",
                    "Actualités",
                    "course",
                    "Odyssea"
                ],
                "title" => item::format_title(
                    "Odyssea : les personnels de l’Institut Mines-Télécom ".
                    "mobilisés pour la lutte contre le cancer du sein"
                ),
                "description" => "Dimanche 2 octobre s'est tenu la course Odyssea où l'Institut Mines-Télécom s'est mobilisé ".
                    "pour soutenir la lutte contre le cancer du sein."
            ], [
                "id" => "https://www.imt.fr/?p=89556",
                "timestamp" => strtotime("Tue, 18 Oct 2022 14:34:00 +0000"),
                "link" => "https://www.imt.fr/nouvelles-start-up-beneficiaires-des-fonds-de-pret-dhonneur".
                    "-imt-numerique-et-industrie-et-energie-4-0/",
                "imageurl" => "https://www.imt.fr/wp-content/uploads/2022/10/start-up-80x80.jpg",
                "categories" => [
                    "À la une",
                    "Actualités",
                    "prêts d'honneur",
                    "start-up"
                ],
                "title" => item::format_title(
                    "Nouvelles start-up bénéficiaires des fonds de prêt d’honneur IMT « Numérique » ".
                    "et « Industrie et Energie 4.0 »"
                ),
                "description" => "Le comité du Fonds IMT Numérique et du Fonds Industrie et Energie 4.0 et se sont réunis".
                    " le 20 septembre et 11 octobre pour attribuer des prêts d'honneurs à de nouvelles start-up."
            ], [
                "id" => "https://www.imt.fr/?p=89147",
                "timestamp" => strtotime("Wed, 05 Oct 2022 12:59:57 +0000"),
                "link" => "https://www.imt.fr/suivez-les-lives-imt-pour-lindustrie-du-futur/",
                "imageurl" => "https://www.imt.fr/wp-content/uploads/2022/05/les-lives_IMT-industrie-du-futur_archer-80x80.png",
                "categories" => [
                    "À la une",
                    "Actualités",
                    "Live IMT industrie",
                    "Live"
                ],
                "title" => item::format_title(
                    "Suivez les Lives IMT pour l’industrie du futur ! Rendez-vous le 20 octobre à 18h30"
                ),
                "description" => "L’Observatoire des métiers et des compétences de l’Institut Mines-Télécom propose," .
                    " depuis mai, une nouvelle série de rendez-vous mensuels consacrés à l’industrie, portés par les " .
                    "étudiantes et étudiants de ses écoles  : les Lives IMT pour l’industrie du futur !"
            ], [
                "id" => "https://www.imt.fr/?p=89403",
                "timestamp" => strtotime("Mon, 03 Oct 2022 10:40:07 +0000"),
                "link" => "https://www.imt.fr/2e-campagne-de-recrutement-2022-rejoignez-limt/",
                "imageurl" => "https://www.imt.fr/wp-content/uploads/2020/03/rejoindre_IMT-80x80.png",
                "categories" => [
                    "À la une",
                    "Actualités",
                    "CDD",
                    "CDI",
                    "emploi",
                    "offre d'emploi",
                    "recrutment"
                ],
                "title" => item::format_title("2e campagne de recrutement 2022 : rejoignez l’IMT"),
                "description" => "Plus de 60 offres d’emploi en CDD ou CDI de droit public à pourvoir."
            ], [
                "id" => "https://www.imt.fr/?p=89382",
                "timestamp" => strtotime("Fri, 30 Sep 2022 14:00:40 +0000"),
                "link" => "https://www.imt.fr/le-fonds-imt-numerique-et-igeu-fetent-leurs-10-ans/",
                "imageurl" => "https://www.imt.fr/wp-content/uploads/2022/09/10_ans_fonds_honneur-80x80.jpg",
                "categories" => [
                    "À la une",
                    "Actualités",
                    "Communiqués de presse",
                    "10 ans",
                    "Fonds IMT Numérique"
                ],
                "title" => item::format_title("Le Fonds IMT Numérique et IGEU fêtent leurs 10 ans"),
                "description" => "Il y a 10 ans, l’Institut Mines Télécom (IMT), la Fondation Mines-Télécom et la Caisse des" .
                    " dépôts créaient Initiative Grandes Ecoles et Université (IGEU) et le Fonds IMT Numérique pour répondre au" .
                    " besoin de financement de jeunes projets d’entreprise."
            ],
        ];
    }
}
