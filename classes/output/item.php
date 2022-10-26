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
use core_text;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Class to help display an RSS Item
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item implements renderable, templatable {

    /** @var string The ID of the item. */
    private $id;

    /** @var moodle_url|null The link of the item. */
    private $link;

    /** @var string|null The title of the item. */
    private $title;

    /** @var string The description of the item. */
    private $description;

    /** @var int|null The timestamp of the item. */
    private $timestamp;

    /** @var bool $showdescription Decides wether to show the description of the item. */
    private $showdescription;

    /** @var moodle_url $imageurl The url to the item's image. */
    public $imageurl;

    /** @var array $categories The categories of the item. */
    private $categories = array();

    /**
     * Contructor
     *
     * @param string $id The id of the RSS item.
     * @param moodle_url $link The URL of the RSS item.
     * @param string $description The description of the RSS item.
     * @param int $timestamp The Unix timestamp that represents the published date.
     * @param boolean $showdescription Whether to show the description.
     * @param string $title The title pf the RSS item.
     * @param moodle_url $imageurl The URL of the item's image.
     * @param array $categories The Categories of the item.
     */
    public function __construct(
        $id,
        $link,
        $description,
        $timestamp,
        $showdescription,
        $title,
        $imageurl,
        $categories
    ) {
        $this->id = $id;
        $this->link = $link;
        $this->title = self::format_title($title);
        $this->description = $description;
        $this->timestamp = $timestamp;
        $this->showdescription = $showdescription;
        $this->imageurl = $imageurl;
        $this->categories = $categories;
    }

    /**
     * Export context for use in mustache templates.
     *
     * @param renderer_base $output
     * @return array
     * @see templatable::export_for_template()
     */
    public function export_for_template(renderer_base $output) : array {

        // TODO find a way to add an image if the item's image url is empty.

        $data = array(
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'link' => clean_param($this->link, PARAM_URL),
            'imageurl' => (new moodle_url($this->imageurl))->out(),
            'categories' => $this->categories
        );

        // If the item does not have a title, create one from the description.
        if (!$this->title) {
            $this->title = strip_tags($this->description);
            $this->title = core_text::substr($this->title, 0, 20) . '...';
        }

        // Allow the renderer to format the title and description if it extends rss_thumbnails\output\renderer.
        if ($output instanceof renderer) {
            $title = strip_tags($output->format_item_title($this->title));
            $description = $output->format_item_description($this->description);
        } else {
            $title = $this->title;
            $description = $this->description;
        }

        $data['title'] = $title;
        $data['description'] = $this->showdescription ? $description : '';

        return $data;
    }

    /**
     * Strips a large title to size and adds ... if title too long
     * This function does not escape HTML entities, so they have to be escaped
     * before being passed here.
     *
     * @param string $title title to shorten.
     * @param int $max max character length of title.
     * @return string title shortened if necessary.
     */
    public static function format_title($title, $max = 64) {
        return (core_text::strlen($title) <= $max) ? $title : core_text::substr($title, 0, $max - 3) . '...';
    }

    /**
     * Gets the link of the item.
     *
     * @return moodle_url|null the link of the item.
     */
    public function get_link(): ?moodle_url {
        return $this->link;
    }

    /**
     * Gets the categories of the item.
     *
     * @return array the categories of the item.
     */
    public function get_categories(): array {
        return $this->categories;
    }

    /**
     * Gets the description of the item.
     *
     * @return string the description of the item.
     */
    public function get_description(): string {
        return $this->description;
    }

    /**
     * Gets the id of the item.
     *
     * @return string the id of the item.
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Gets the imageurl of the item.
     *
     * @return string|null the image url of the item.
     */
    public function get_imageurl(): ?string {
        return $this->imageurl;
    }

    /**
     * Gets the timestamp of the item.
     *
     * @return int|null the timestamp of the item.
     */
    public function get_timestamp(): ?int {
        return $this->timestamp;
    }

    /**
     * Gets the title of the item.
     *
     * @return string|null the title of the item.
     */
    public function get_title(): ?string {
        return $this->title;
    }
}
