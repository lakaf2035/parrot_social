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
 * @package    local_parrot_social
 * @copyright  2019 Digital Education Society (http://www.dibig.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_parrot_social\Models\Invitation;

defined('MOODLE_INTERNAL') || die;

function xmldb_local_parrot_social_upgrade($oldversion) {
    global $DB;
    $db_man = $DB->get_manager();

    if ($oldversion < 1500000000) {
        // create parrot_social_friends table as in install.xml
        $table = new xmldb_table('parrot_social_friends');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('parrot1_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('parrot2_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('friend_parrot1_key', XMLDB_KEY_FOREIGN, array('parrot1_id'), 'parrot_social_parrots', array('parrot_id'));
        $table->add_key('friend_parrot2_key', XMLDB_KEY_FOREIGN, array('parrot2_id'), 'parrot_social_parrots', array('parrot_id'));
        $db_man->create_table($table);

        // create parrot_social_invitations table as in install.xml
        $table = new xmldb_table('parrot_social_invitations');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('recipient_parrot_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sending_parrot_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('invitation_recipient_parrot_key', XMLDB_KEY_FOREIGN, array('recipient_parrot_id'), 'parrot_social_parrots', array('parrot_id'));
        $table->add_key('invitation_sending_parrot_key', XMLDB_KEY_FOREIGN, array('sending_parrot_id'), 'parrot_social_parrots', array('parrot_id'));
        $db_man->create_table($table);

        upgrade_plugin_savepoint(true, 1500000000, 'local', 'parrot_social');
    }

    if ($oldversion < 1500000001) {
        // add forgotten status column
        $table = new xmldb_table('parrot_social_invitations');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '255', null, null, null, Invitation::STATUS_PENDING);
        if (!$db_man->field_exists($table, $field)) {
            $db_man->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 1500000001, 'local', 'parrot_social');
    }

    if ($oldversion < 2023040901) {
        // Get parrot table
        $table = new xmldb_table('parrot_social_parrots');
        // rename table parrot_social_parrots_old
        $db_man->rename_table($table, 'parrot_social_parrots_old');


        // Create new table
        $table = new xmldb_table('parrot_social_parrots');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_field('parrot_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('picture_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('custom_attribute', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $db_man->create_table($table);

        // Copy data from old table to new table
        $DB->execute('INSERT INTO {parrot_social_parrots} (parrot_id, picture_id, custom_attribute) SELECT parrot_id, picture_id, custom_attribute FROM {parrot_social_parrots_old}');

        // Drop old table
        $oldtable = new xmldb_table('parrot_social_parrots_old');
        $db_man->drop_table($oldtable);

        upgrade_plugin_savepoint(true, 2023040901, 'local', 'parrot_social');
    }

    return true;
}
