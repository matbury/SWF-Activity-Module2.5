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

/**
 * Define the complete swf structure for backup, with file and id annotations
 */     
class backup_swf_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
        // Define each element separated
        $swf = new backup_nested_element('swf', array('id'),
                array('name',
                    'intro',
                    'introformat',
                    'swfurl',
                    'xmlurl',
                    'fileurl',
                    'version',
                    'apikey',
                    'scale',
                    'salign',
                    'pagecolor',
                    'bgcolor',
                    'seamlesstabbing',
                    'allowfullscreen',
                    'allowscriptaccess',
                    'allownetworking',
                    'name1',
                    'value1',
                    'name2',
                    'value2',
                    'name3',
                    'value3',
                    'configxml',
                    'width',
                    'height',
                    'maxgrade',
                    'grade',
                    'exiturl',
                    'timecreated',
                    'timemodified',
                    'usermodified'));
        /*$sessions = new backup_nested_element('sessions');
        $session = new backup_nested_element('session', array('id'), array(
            'session_id', 'user_id', 'session_datetime', 'project_name',
            'session_key', 'session_code', 'session_context'));
        $activities = new backup_nested_element('sessionactivities');
        $activity = new backup_nested_element('sessionactivity', array('id'), array(
            'session_id', 'activity_id', 'activity_name', 'num_actions', 'score',
            'activity_solved', 'qualification', 'total_time', 'activity_code'));*/
        // Build the tree
        /*$swf->add_child($sessions);
        $sessions->add_child($session);
        $session->add_child($activities);
        $activities->add_child($activity);*/
        // Define sources
        $swf->set_source_table('swf', array('id' => backup::VAR_ACTIVITYID));
        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            //$session->set_source_table('swf_sessions', array('swfid' => backup::VAR_PARENTID));
            //$activity->set_source_table('swf_activities', array('session_id' => '../../session_id'));
        }
        // Define id annotations
        //$swf->annotate_ids('scale', 'grade');
        //$session->annotate_ids('user', 'user_id');
        // Define file annotations
        $swf->annotate_files('mod_swf', 'intro', null);     // This file area has no itemid
        $swf->annotate_files('mod_swf', 'content', null);   // This file area has no itemid
        // Return the root element (swf), wrapped into standard activity structure
        return $this->prepare_activity_structure($swf);
    }
}