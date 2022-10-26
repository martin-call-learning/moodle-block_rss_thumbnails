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
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_rss_thumbnails\output\block;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot.'/blocks/rss_thumbnails/edit_form.php');

/**
 * Class block_rss_thumbnails_edit_form
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rss_thumbnails_edit_form extends block_edit_form {
    /**
     * Creates a form to define caroussel's parameteters
     *
     * @param HTML_QuickForm $mform
     * @throws coding_exception
     * @return void
     */
    protected function specific_definition($mform) {
        global $CFG, $DB, $USER, $SESSION;

        // Fields for editing block contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement(
                'selectyesno',
                'config_display_description',
                get_string('displaydescriptionlabel', 'block_rss_thumbnails')
        );
        $mform->setDefault('config_display_description', 0);

        $mform->addElement(
                'text',
                'config_numentries',
                get_string('numentrieslabel', 'block_rss_thumbnails'),
                array('size' => block_rss_thumbnails::DEFAULT_MAX_ENTRIES)
        );
        $mform->setType('config_numentries', PARAM_INT);
        $mform->addRule('config_numentries', null, 'numeric', null, 'client');
        if (!empty($CFG->block_rss_thumbnails_num_entries)) {
            $mform->setDefault('config_numentries', $CFG->block_rss_thumbnails_num_entries);
        } else {
            $mform->setDefault('config_numentries', 5);
        }

        $insql = '';
        $params = array('userid' => $USER->id);
        if (!empty($this->block->config) && !empty($this->block->config->rssid)) {
            list($insql, $inparams) = $DB->get_in_or_equal($this->block->config->rssid, SQL_PARAMS_NAMED);
            $insql = "OR id $insql ";
            $params += $inparams;
        }

        $titlesql = "CASE WHEN {$DB->sql_isempty('block_rss_thumbnails','preferredtitle', false, false)}
                      THEN {$DB->sql_compare_text('title', 64)} ELSE preferredtitle END";

        $rssfeeds = $DB->get_records_sql_menu("
                SELECT id, $titlesql
                  FROM {block_rss_thumbnails}
                 WHERE userid = :userid OR shared = 1 $insql
                 ORDER BY $titlesql",
                $params);

        if ($rssfeeds) {
            $select = $mform->addElement(
                    'select',
                    'config_rssid',
                    get_string('choosefeedlabel', 'block_rss_thumbnails'),
                    $rssfeeds
            );
            $select->setMultiple(true);
        } else {
            $mform->addElement('static', 'config_rssid_no_feeds', get_string('choosefeedlabel', 'block_rss_thumbnails'),
                    get_string('nofeeds', 'block_rss_thumbnails'));
        }

        if (has_any_capability(
                array('block/rss_thumbnails:manageanyfeeds', 'block/rss_thumbnails:manageownfeeds'),
                $this->block->context)
        ) {
            $mform->addElement('static', 'nofeedmessage', '',
                    '<a href="' .
                    $CFG->wwwroot . '/blocks/rss_thumbnails/managefeeds.php?courseid=' .
                    $this->page->course->id .
                    '">' .
                    get_string('feedsaddedit', 'block_rss_thumbnails') .
                    '</a>');
        }
        $mform->addElement('text', 'config_title', get_string('uploadlabel'));
        $mform->setType('config_title', PARAM_NOTAGS);
        $mform->setDefault('config_title', block_rss_thumbnails::DEFAULT_TITLE);

        $mform->addElement('selectyesno', 'config_block_rss_thumbnails_show_channel_link', get_string(
                'clientshowchannellinklabel',
                'block_rss_thumbnails')
        );
        $mform->setDefault('config_block_rss_thumbnails_show_channel_link', 0);

        $mform->addElement(
                'selectyesno',
                'config_block_rss_thumbnails_show_channel_image',
                get_string('clientshowimagelabel', 'block_rss_thumbnails')
        );
        $mform->setDefault('config_block_rss_thumbnails_show_channel_image', 0);
        $mform->removeElement('config_block_rss_thumbnails_show_channel_image');
        $mform->removeElement('config_block_rss_thumbnails_show_channel_link');

        $mform->addElement(
                'text',
                'config_carousseldelay',
                get_string('carousseldelay', 'block_rss_thumbnails')
        );
        $mform->setDefault('config_carousseldelay', block_rss_thumbnails::DEFAULT_CAROUSSEL_DELAY);
        $mform->setType('config_carousseldelay', PARAM_INT);

        $mform->addElement(
                'selectyesno',
                'config_show_channel_link',
                get_string('clientshowchannellinklabel', 'block_rss_thumbnails')
        );
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
