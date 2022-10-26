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
 * Contains class block_rss_thumbnails\output\footer
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails\output;

use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class to display an RSS Block Thumbnails footer
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements renderable, templatable {

    /**
     * The link provided in the RSS channel
     *
     * @var moodle_url|null
     */
    private $channelurl;

    /**
     * Link to manage feeds, only provided if a feed has failed.
     *
     * @var moodle_url|null
     */
    private $manageurl = null;

    /**
     * Constructor
     *
     * @param moodle_url|null $channelurl (optional) The link provided in the RSS channel
     */
    public function __construct(moodle_url $channelurl = null) {
        $this->channelurl = $channelurl;
    }

    /**
     * Get the channel url
     *
     * @return moodle_url
     */
    public function get_channelurl() {
        return $this->channelurl;
    }

    /**
     * Gets the manage url
     *
     * @return moodle_url|null
     */
    public function get_manageurl() {
        return $this->manageurl;
    }

    /**
     * Record the fact that there is at least one failed feed (and the URL for viewing
     * these failed feeds).
     *
     * @param moodle_url $manageurl the URL to link to for more information
     */
    public function set_failed(moodle_url $manageurl) {
        $this->manageurl = $manageurl;
    }

    /**
     * Export context for use in mustache templates
     *
     * @see templatable::export_for_template()
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->channellink = clean_param($this->channelurl, PARAM_URL);
        if ($this->manageurl) {
            $data->hasfailedfeeds = true;
            $data->manageurl = clean_param($this->manageurl, PARAM_URL);
        }

        return $data;
    }
}
