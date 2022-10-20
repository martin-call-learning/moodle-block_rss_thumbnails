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
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails\output;

use renderer_base;

/**
 * Class to help display an RSS Feeds block
 *
 * @package   block_rss_thumbnails
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block extends \block_rss_client\output\block {

    /** @var int The delay between two slides */
    protected int $carousselspeed = 0;
    /**
     * Contruct
     *
     * @param int $carousselspeed the caroussel speed of the block
     * @param array $feeds An array of renderable feeds
     */
    public function __construct($carousselspeed, array $feeds = array()) {
        parent::__construct($feeds);
        $this->carousselspeed = $carousselspeed;
    }

    /**
     * Prepare data for use in a template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $data = parent::export_for_template($output);
        $data['carousselspeed'] = $this->carousselspeed;
        return $data;
    }
}
