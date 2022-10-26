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
 * Contains class block_rss_client\output\feed
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails\output;

use block_rss_thumbnails;
use renderable;
use renderer_base;
use templatable;

/**
 * Class to help display an RSS Feeds block
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block implements renderable, templatable {

    /** @var int The delay between two slides of the caroussel (in milliseconds) */
    private $carousseldelay;

    /** @var array An array of renderable feeds */
    private $feeds;

    /**
     * Contructor
     *
     * @param int $carousseldelay An integer representing the speed of the carroussel
     * @param array $feeds An array of renderable feeds
     */
    public function __construct(int $carousseldelay = block_rss_thumbnails::DEFAULT_CAROUSSEL_DELAY, array $feeds = array()) {
        $this->feeds = $feeds;
        $this->carousseldelay = $carousseldelay;
    }

    /**
     * Prepare data for use in a template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $data = array(
            'feeds' => array(),
            'carousseldelay' => $this->carousseldelay
        );
        foreach ($this->feeds as $feed) {
            $data['feeds'][] = $feed->export_for_template($output);
        }

        return $data;
    }

    /**
     * Adds a feed
     *
     * @param feed $renderablefeed
     * @return void
     */
    public function add_feed(feed $renderablefeed) {
        $this->feeds[] = $renderablefeed;
    }

    /**
     * Get feeds
     *
     * @return array
     */
    public function get_feeds(): array {
        return $this->feeds;
    }
}
