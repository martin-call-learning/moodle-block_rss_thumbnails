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
 * Upgrade
 *
 * @package   block_rss_thumbnails
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the block_rss_client database.
 *
 * @param int $oldversion The version number of the plugin that was installed.
 * @return boolean
 */
function xmldb_block_rss_thumbnails_upgrade($oldversion) {
    global $DB;

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.
    $dbman = $DB->get_manager();
    if ($oldversion < 2022111004) {

        $table = create_block_rss_thumbnails_table();
        $params = ['blockname' => 'rss_thumbnails'];

        // Conditionally launch create table for block_rss_thumbnails.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        if ($DB->record_exists('block_instances', $params)) {
            if ($dbman->table_exists('block_rss_client')) {
                $rssfeeds = $DB->get_records('block_rss_client');
                $DB->insert_records('block_rss_thumbnails', $rssfeeds);
            }
            $blockinstances = $DB->get_records('block_instances', ['blockname' => 'rss_thumbnails']);
            foreach ($blockinstances as $blockinstance) {
                // Access to block's configdata.
                $blockconfig = unserialize(base64_decode($blockinstance->configdata));

                // Update caroussel speed variable that changed into carousseldelay for semantical reasons.
                $blockconfig->carousseldelay = $blockconfig->carousselspeed;
                $blockconfig->numentries = $blockconfig->shownumentries;
                //unset($blockconfig->carousselspeed);

                $newblockinstance = clone $blockinstance;
                // Re-serialize block's configdata.
                $newblockinstance->configdata = base64_encode(serialize($blockconfig));

                $DB->update_record('block_instances', $newblockinstance);
            }
        }

        // Rss_thumbnails savepoint reached.
        upgrade_block_savepoint(true, 2022120500, 'rss_thumbnails');
    }

    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}

/**
 * Creates an empty block_rss_thumbnails table by adding to it all the fields it needs and setting up the right primary key.
 *
 * @return xmldb_table
 */
function create_block_rss_thumbnails_table(): xmldb_table {
    // Define table block_rss_thumbnails to be created.
    $table = new xmldb_table('block_rss_thumbnails');

    // Adding fields to table block_rss_thumbnails.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
    $table->add_field('preferredtitle', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
    $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
    $table->add_field('shared', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('skiptime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('skipuntil', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    // Adding keys to table block_rss_thumbnails.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    return $table;
}
