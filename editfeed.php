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
 * Script to let a user edit the properties of a particular RSS feed.
 *
 * @package   block_rss_thumbnails
 * @copyright 2022 - CALL Learning - Martin CORNU-MANSUY <martin@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rss_thumbnails;

require_once(__DIR__ . '/../../config.php');
require_login();

use block_rss_thumbnails\form\feed_edit;
use context_system;
use moodle_url;
use stdClass;

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);
$rssid = optional_param('rssid', 0, PARAM_INT); // 0 mean create new.

if ($courseid == SITEID) {
    $courseid = 0;
}
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$managesharedfeeds = has_capability('block/rss_thumbnails:manageanyfeeds', $context);
if (!$managesharedfeeds) {
    require_capability('block/rss_thumbnails:manageownfeeds', $context);
}

$urlparams = array('rssid' => $rssid);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
$managefeeds = new moodle_url('/blocks/rss_thumbnails/managefeeds.php', $urlparams);

$PAGE->set_url('/blocks/rss_thumbnails/editfeed.php', $urlparams);
$PAGE->set_pagelayout('admin');

if ($rssid) {
    $isadding = false;
    $rssrecord = $DB->get_record('block_rss_thumbnails', array('id' => $rssid), '*', MUST_EXIST);
} else {
    $isadding = true;
    $rssrecord = new stdClass();
}

$mform = new feed_edit($PAGE->url, $isadding, $managesharedfeeds);
$mform->set_data($rssrecord);

if ($mform->is_cancelled()) {
    redirect($managefeeds);

} else if ($data = $mform->get_data()) {
    $data->userid = $USER->id;
    if (!$managesharedfeeds) {
        $data->shared = 0;
    }

    if ($isadding) {
        $DB->insert_record('block_rss_thumbnails', $data);
    } else {
        $data->id = $rssid;
        $DB->update_record('block_rss_thumbnails', $data);
    }

    redirect($managefeeds);

} else {
    if ($isadding) {
        $strtitle = get_string('addnewfeed', 'block_rss_thumbnails');
    } else {
        $strtitle = get_string('editafeed', 'block_rss_thumbnails');
    }

    $PAGE->set_title($strtitle);
    $PAGE->set_heading($strtitle);

    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_rss_thumbnails'));
    $PAGE->navbar->add(get_string('managefeeds', 'block_rss_thumbnails'), $managefeeds );
    $PAGE->navbar->add($strtitle);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle, 2);

    $mform->display();

    echo $OUTPUT->footer();
}

