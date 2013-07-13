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
 * @package    mod
 * @subpackage swf
 * @copyright  2013 Matt Bury
 * @author     Matt Bury <matt@matbury.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute swf upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_swf_upgrade($oldversion) {
    global $CFG, $DB;

    //$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    /*if ($oldversion < 2011011900) {
        $table = new xmldb_table('swf');
        /// Define lang field format to be added to swf
        $field = new xmldb_field('fieldname');
        $field->set_attributes(XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'ca', 'url');
        $result = $result && $dbman->add_field($table, $field);

        /// Define exiturl field format to be added to swf
        $field = new xmldb_field('fieldname');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'url');
        $result = $result && $dbman->add_field($table, $field);
    }*/
    // Final return of upgrade result (true, all went well) to Moodle.
    return true;
}
