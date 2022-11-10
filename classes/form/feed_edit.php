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


namespace block_rss_thumbnails\form;

defined('MOODLE_INTERNAL') || die();

require_login();
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir .'/simplepie/moodle_simplepie.php');

use moodle_simplepie;
use moodle_url;
use moodleform;

/**
 * A class to be able to edit the feeds we import in the plugin.
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed_edit extends moodleform {

    // TODO review this file.

    /** @var bool $isadding checks whether the user is adding a new feed or not. */
    protected $isadding;

    /** @var bool $caneditshared checks whether the user has the capability to edit a shared feed or not. */
    protected $caneditshared;

    /** @var string $title The title */
    protected $title = '';

    /** @var string $description The description */
    protected $description = '';

    /**
     * Constructor.
     *
     * @param string $actionurl the url of the action.
     * @param bool $isadding to know if the user is adding a new feed or not.
     * @param bool $caneditshared to know if the user has the ability to edit a shared feed or not.
     */
    public function __construct($actionurl, $isadding, $caneditshared) {
        $this->isadding = $isadding;
        $this->caneditshared = $caneditshared;
        parent::__construct($actionurl);
    }

    /**
     * Defines the form allowing to edit a feed.
     *
     * @return void
     */
    public function definition() {
        $mform =& $this->_form;

        // Then show the fields about where this block appears.
        $mform->addElement('header', 'rsseditfeedheader', get_string('feed', 'block_rss_thumbnails'));

        $mform->addElement('text', 'url', get_string('feedurl', 'block_rss_thumbnails'), array('size' => 60));
        $mform->setType('url', PARAM_URL);
        $mform->addRule('url', null, 'required');

        $mform->addElement('checkbox', 'autodiscovery', get_string('enableautodiscovery', 'block_rss_thumbnails'));
        $mform->setDefault('autodiscovery', 1);
        $mform->setAdvanced('autodiscovery');
        $mform->addHelpButton('autodiscovery', 'enableautodiscovery', 'block_rss_thumbnails');

        $mform->addElement('text', 'preferredtitle', get_string('customtitlelabel', 'block_rss_thumbnails'), array('size' => 60));
        $mform->setType('preferredtitle', PARAM_NOTAGS);

        if ($this->caneditshared) {
            $mform->addElement('selectyesno', 'shared', get_string('sharedfeed', 'block_rss_thumbnails'));
            $mform->setDefault('shared', 0);
        }

        $submitlabal = null; // Default.
        if ($this->isadding) {
            $submitlabal = get_string('addnewfeed', 'block_rss_thumbnails');
        }
        $this->add_action_buttons(true, $submitlabal);
    }

    /**
     * Defines the form after the discovery of data
     *
     * @return void
     */
    public function definition_after_data() {
        $mform =& $this->_form;

        if ($mform->getElementValue('autodiscovery')) {
            $mform->applyFilter('url', self::class . '::autodiscover_feed_url');
        }
    }

    /**
     * Validates the edition form.
     *
     * @param array $data Datas of the form.
     * @param array $files Files of the form.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        $rss = new moodle_simplepie();
        // Set timeout for longer than normal to try and grab the feed.
        $rss->set_timeout();
        $rss->set_feed_url($data['url']);
        $rss->set_autodiscovery_cache_duration(0);
        $rss->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
        $rss->init();

        if ($rss->error()) {
            $errors['url'] = get_string('couldnotfindloadrssfeed', 'block_rss_thumbnails');
        } else {
            $this->title = $rss->get_title();
            $this->description = $rss->get_description();
        }

        return $errors;
    }

    /**
     * Gets data of the form.
     *
     * @return object|null
     */
    public function get_data(): ?object {
        $data = parent::get_data();
        if ($data) {
            $data->title = '';
            $data->description = '';

            if ($this->title) {
                $data->title = $this->title;
            }

            if ($this->description) {
                $data->description = $this->description;
            }
        }
        return $data;
    }

    /**
     * Autodiscovers a feed url from a given url, to be used by the formslibs
     * filter function
     *
     * Uses simplepie with autodiscovery set to maximum level to try and find
     * a feed to subscribe to.
     * See: http://simplepie.org/wiki/reference/simplepie/set_autodiscovery_level
     *
     * @param string $url URL to autodiscover a url
     * @return string URL of feed or original url if none found
     */
    public static function autodiscover_feed_url($url): string {
        $rss = new moodle_simplepie();
        $rss->set_feed_url($url);
        $rss->set_autodiscovery_level();
        // When autodiscovering an RSS feed, simplepie will try lots of
        // rss links on a page, so set the timeout high.
        $rss->set_timeout(20);
        $rss->init();

        if ($rss->error()) {
            return $url;
        }

        // Return URL without quoting..
        $discoveredurl = new moodle_url($rss->subscribe_url());
        return $discoveredurl->out(false);
    }
}
