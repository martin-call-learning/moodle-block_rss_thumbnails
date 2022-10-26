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
 * Contains class {@see block_rss_client\output\feed}.
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
 * Class to represent an RSS feed.
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed implements renderable, templatable {

    /** @var string|null The feed's title. */
    private $title;

    /** @var array An array of renderable feed items. */
    private $items = array();

    /** @var channel_image|null The channel image. */
    private $image;

    /** @var boolean Whether to show the title. */
    private $showtitle;

    /** @var boolean Whether to show the channel image. */
    private $showimage;

    /** @var moodle_url The URL of the feed's link. */
    private $link;

    /**
     * Contructor.
     *
     * @param string|null $title The title of the RSS feed.
     * @param moodle_url $link The link of the RSS feed.
     * @param channel_image|null $image The image of the RSS feed.
     * @param boolean $showtitle Whether to show the title.
     * @param boolean $showimage Whether to show the channel image.
     */
    public function __construct(
        $title,
        $link,
        $image = null,
        $showtitle = true,
        $showimage = true
    ) {
        $this->title = $title;
        $this->link = $link;
        $this->image = $image;
        $this->showtitle = $showtitle;
        $this->showimage = $showimage;
    }

    /**
     * Adds an item into the feed.
     *
     * @param item $item The item to add.
     * @return void
     */
    public function add_item(item $item) {
        $this->items[] = $item;
    }

    /**
     * Gets the feed items.
     *
     * @return array
     */
    public function get_items() {
        return $this->items;
    }

    /**
     * Gets the feed's image.
     *
     * @return channel_image
     */
    public function get_image() {
        return $this->image;
    }

    /**
     * Gets the link of the feed.
     *
     * @return moodle_url
     */
    public function get_link() {
        return $this->link;
    }

    /**
     * Gets the title of the feed.
     *
     * @return string|null
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Export this for use in a mustache template context.
     *
     * @param renderer_base $output
     * @return array
     * @see templatable::export_for_template()
     */
    public function export_for_template(renderer_base $output) {
        $data = array(
            'title' => $this->showtitle ? $this->title : '',
            'items' => array(),
        );

        $data['image'] = ($this->showimage && $this->image) ? $this->image->export_for_template($output) : null;

        foreach ($this->items as $item) {
            $data['items'][] = $item->export_for_template($output);
        }

        return $data;
    }
}
