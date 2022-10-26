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
 * Contains class block_rss_thumbnails\output\channel_image
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails\output;

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Class to display RSS channel images
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 CALL Learning
 * @author    Martin CORNU-MANSUY <martin@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class channel_image implements renderable, templatable {

    /**
     * The URL location of the image
     *
     * @var string
     */
    private $url;

    /**
     * The title of the image
     *
     * @var string
     */
    private $title;

    /**
     * The URL of the image link
     *
     * @var string
     */
    protected $link;

    /**
     * Contructor
     *
     * @param moodle_url $url The URL location of the image
     * @param string $title The title of the image
     * @param moodle_url|null $link The URL of the image link
     */
    public function __construct(moodle_url $url, string $title, ?moodle_url $link = null) {
        $this->url      = $url;
        $this->title    = $title;
        $this->link     = $link;
    }

    /**
     * Export this for use in a mustache template context.
     *
     * @see templatable::export_for_template()
     * @param renderer_base $output
     * @return array The data for the template
     */
    public function export_for_template(renderer_base $output) {
        return array(
                'url'   => clean_param($this->url, PARAM_URL),
                'title' => $this->title,
                'link'  => clean_param($this->link, PARAM_URL),
        );
    }

    /**
     * Get the URL
     *
     * @return moodle_url
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * Get the link
     *
     * @return moodle_url
     */
    public function get_link() {
        return $this->link;
    }
}
