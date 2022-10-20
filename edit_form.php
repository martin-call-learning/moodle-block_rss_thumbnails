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
 * Edit Form
 *
 * @package   block_rss_thumbnails
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot.'/blocks/rss_client/edit_form.php');

/**
 * Class block_rss_thumbnails_edit_form
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rss_thumbnails_edit_form extends block_rss_client_edit_form {
    /**
     * Creates a form to define caroussel's parameteters
     *
     * @param HTML_QuickForm $mform
     * @throws coding_exception
     * @return void
     */
    protected function specific_definition($mform) {
        parent::specific_definition($mform);
        $mform->removeElement('config_block_rss_client_show_channel_image');
        $mform->removeElement('config_block_rss_client_show_channel_link');

        $mform->addElement('text', 'config_carousselspeed', get_string('carousselspeed', 'block_rss_thumbnails'));
        $mform->setDefault('config_carousselspeed', block_rss_thumbnails::DEFAULT_CAROUSSEL_SPEED);
        $mform->setType('config_carousselspeed', PARAM_INT);

        $mform->addElement('selectyesno', 'config_show_channel_link', get_string('clientshowchannellinklabel', 'block_rss_client'));
        $mform->setDefault('config_show_channel_link', false);
        $mform->setType('config_show_channel_link', PARAM_BOOL);

        $mform->addElement(
                'selectyesno',
                'config_remove_image_size_suffix',
                get_string('removeimagesizesuffix', 'block_rss_thumbnails')
        );
        $mform->setDefault('config_remove_image_size_suffix', false);
        $mform->setType('config_remove_image_size_suffix', PARAM_BOOL);
    }
}
