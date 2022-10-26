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
 * Contains class block_rss_client\output\block_renderer_html
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails\output;

use moodle_page;
use plugin_renderer_base;

/**
 * Renderer for RSS Thumbnails block, any renderer for an RSS Thumbnails Block should extend this class.
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Format an RSS thumbnails item title
     *
     * @param string $title
     * @return string
     */
    public function format_item_title($title): string {
        return break_up_long_words($title, 30);
    }

    /**
     * Format an RSS thumbnails item description
     *
     * @param string $descritpion
     * @return string
     */
    public function format_item_description($descritpion): string {
        return $descritpion;
    }
}

