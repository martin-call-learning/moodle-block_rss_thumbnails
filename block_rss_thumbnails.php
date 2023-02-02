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
 * RSS Thumbnail Block
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_rss_thumbnails\output\block;
use block_rss_thumbnails\output\feed;
use block_rss_thumbnails\output\footer;
use block_rss_thumbnails\feed_factory;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * Class block_rss_thumbnails
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rss_thumbnails extends block_base {

    // TODO Think about adding a slider to allow navigation between feed items.
    // TODO Add a message when there is no internet connection or if the rss feed can't be found.

    /** @var int The default delay between 2 slides */
    const DEFAULT_CAROUSSEL_DELAY = 4000;

    /** @var string The default name of the block */
    const DEFAULT_TITLE = "RSS Thumbnail";

    /** @var int The maximum number of item entries for a feed by default */
    const DEFAULT_MAX_ENTRIES = 5;

    /** @var bool Track whether any of the output feeds have recorded failures */
    private $hasfailedfeeds = false;
    /** @var int Defines the number of maximum feeds in the thumbnail */
    private $maxentries = self::DEFAULT_MAX_ENTRIES;

    /**
     * Init function
     */
    public function init(): void {

        $this->title = get_string('pluginname', 'block_rss_thumbnails');

        // Initialise content.
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
    }

    /**
     * Content for the block
     *
     * @return stdClass|null
     * @throws coding_exception
     */
    public function get_content() {
        global $DB;

        if ($this->content != null && !empty($this->content->text)) {
            return $this->content;
        }

        $this->page->requires->css(
            new moodle_url('/blocks/rss_thumbnails/js/glide/dist/css/glide.core' .
                (debugging() ? '.min' : '') . '.css'));

        if (!$this->config_is_valid()) {
            $this->content->text = get_string("invalidconfig", "block_rss_thumbnails");
            return $this->content;
        }

        if (!isset($this->config)) {
            // The block has yet to be configured - just display configure message in
            // the block if user has permission to configure it.

            if (has_capability('block/rss_thumbnails:manageanyfeeds', $this->context)) {
                $this->content->text = get_string('configureblock', 'block_rss_thumbnails');
            }

            return $this->content;
        }
        $carousseldelay = $this->config->carousseldelay ?? self::DEFAULT_CAROUSSEL_DELAY;
        $block = new block($carousseldelay, $this->config->remove_image_size_suffix ?? false);

        if (!empty($this->config->rssid)) {
            list($rssidssql, $params) = $DB->get_in_or_equal($this->config->rssid);
            $rssfeeds = $DB->get_records_select('block_rss_thumbnails', "id $rssidssql", $params);

            foreach ($rssfeeds as $feed) {
                $renderablefeed = $this->get_feed($feed, $this->maxentries);
                if ($renderablefeed) {
                    $block->add_feed($renderablefeed);
                }
            }

            $footer = $this->get_footer($rssfeeds);
        }

        $renderer = $this->page->get_renderer('block_rss_thumbnails');
        $this->content = (object) [
            'text' => $renderer->render($block),
            'footer' => $footer ? $renderer->render($footer) : ''
        ];
        return $this->content;
    }

    /**
     * Gets the footer, which is the channel link of the last feed in our list of feeds
     *
     * @param array $feedrecords The feed records from the database
     * @param int $maxentries The max number of items in the footer feed
     * @return footer|null The renderable footer or null if none should be displaye
     */
    protected function get_footer($feedrecords, $maxentries = self::DEFAULT_MAX_ENTRIES): ?footer {
        $footer = null;

        if (!empty($this->config->show_channel_link)) {
            $feedrecord = array_pop($feedrecords);
            $channellink = new moodle_url($feedrecord->url);

            if (!empty($channellink)) {
                $footer = new footer($channellink);
            }
        }

        if ($this->hasfailedfeeds) {
            if (
                has_any_capability(['block/rss_thumbnails:manageownfeeds', 'block/rss_thumbnails:manageanyfeeds'], $this->context)
            ) {
                if ($footer === null) {
                    $footer = new footer();
                }
                $manageurl = new moodle_url('/blocks/rss_thumbnails/managefeeds.php',
                    ['courseid' => $this->page->course->id]);
                $footer->set_failed($manageurl);
            }
        }

        return $footer;
    }

    /**
     * Returns the html of a feed to be displaed in the block
     *
     * @param mixed $feedrecord The feed record from the database
     * @param int $maxentries The maximum number of entries to be displayed
     * @param boolean $showtitle Should the feed title be displayed in html
     * @return block_rss_thumbnails\output\feed|null The renderable feed or null of there is an error
     */
    public function get_feed($feedrecord, $maxentries, $showtitle = true): ?feed {

        if ($feedrecord->skipuntil) {
            // Last attempt to gather this feed via cron failed - do not try to fetch it now.
            $this->hasfailedfeeds = true;
            return null;
        }
        return feed_factory::create_feed_from_url($feedrecord->url, $maxentries, $showtitle);

    }

    /**
     * Serialize and store config data
     *
     * @param stdClass $data
     * @param false $nolongerused
     * @throws coding_exception
     */
    public function instance_config_save($data, $nolongerused = false) {
        parent::instance_config_save($data, $nolongerused);
        cache_helper::purge_by_event('block_rss_thumbnails/expiresfeed');
    }

    /**
     * Strips a large title to size and adds ... if title too long
     * This function does not escape HTML entities, so they have to be escaped
     * before being passed here.
     *
     * @param string $title title to shorten
     * @param int $max max character length of title
     * @return string title shortened if necessary
     */
    public function format_title($title, $max = 64): string {
        return (core_text::strlen($title) <= $max) ? $title : core_text::substr($title, 0, $max - 3) . '...';
    }
    /**
     * Checks wether the configuration of the block is valid or not.
     *
     * @return bool true if the configuration of the block is valid, false if it's not.
     */
    public function config_is_valid(): bool {
        if (empty($this->config)) {
            return false;
        }
        if (empty($this->config->carousseldelay) || !is_integer($this->config->carousseldelay)) {
            return false;
        }
        if (empty($this->config->numentries) || !is_integer($this->config->numentries)) {
            return false;
        }
        if (!$this->config->title) {
            return false;
        }
        return true;
    }
}
