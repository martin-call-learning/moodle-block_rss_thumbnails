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
use core_text;
use moodle_url;
use renderer_base;

/**
 * Class to help display an RSS Item
 *
 * @package   block_rss_thumbnails
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item extends \block_rss_client\output\item {
    /** @var ?string The url of the RSS item's image  */
    protected $imageurl = null;

    /** @var  ?array The categories of the RSS item*/
    protected $categories = null;

    /**
     * Contructor
     *
     * @param string $id The id of the RSS item
     * @param moodle_url $link The URL of the RSS item
     * @param string $title The title pf the RSS item
     * @param string $description The description of the RSS item
     * @param moodle_url $permalink The permalink of the RSS item
     * @param int $timestamp The Unix timestamp that represents the published date
     * @param boolean $showdescription Whether to show the description
     * @param string $imageurl the image's url of the item
     * @param array $categories the categories of the item
     */
    public function __construct($id, moodle_url $link,
        $title, $description,
        moodle_url $permalink,
        $timestamp,
        $showdescription = true,
        $imageurl = null,
        $categories
    ) {
        parent::__construct($id, $link, $title, $description, $permalink, $timestamp, $showdescription);
        $this->imageurl = $imageurl;
        $this->categories = $categories;
    }

    /**
     * Export context for use in mustache templates
     *
     * @param renderer_base $output
     * @return array
     * @see templatable::export_for_template()
     */
    public function export_for_template(renderer_base $output) : array {
        $data = array(
            'id' => $this->id,
            'permalink' => clean_param($this->permalink, PARAM_URL),
            'timestamp' => $this->timestamp,
            'link' => clean_param($this->link, PARAM_URL),
            'imageurl' => (new moodle_url($this->imageurl))->out(),
            'categories' => $this->categories
        );

        // If the item does not have a title, create one from the description.
        $title = $this->title;
        if (!$title) {
            $title = strip_tags($this->description);
            $title = core_text::substr($title, 0, 20) . '...';
        }

        // Allow the renderer to format the title and description.
        $data['title'] = strip_tags($output->format_title($title));
        $data['description'] = $this->showdescription ? $output->format_description($this->description) : null;

        return $data;
    }
}
