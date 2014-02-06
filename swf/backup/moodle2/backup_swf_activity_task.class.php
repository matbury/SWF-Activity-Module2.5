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
 * @package    mod_swf
 * @copyright  2013 Matt Bury
 * @author     Matt Bury <matt@matbury.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/swf/locallib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/swf/backup/moodle2/backup_swf_stepslib.php'); // Because it exists (must)
 
/**
 * swf backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_swf_activity_task extends backup_activity_task {
 
    //Define (add) particular settings this activity can have
    protected function define_my_settings() {
        // No particular settings for this activity
    }
    
    //Define (add) particular steps this activity can have
    protected function define_my_steps() {
        $this->add_step(new backup_swf_activity_structure_step('swf_structure', 'swf.xml'));        
    }
 
    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     * 
     * @global type $CFG
     * @param type $content
     * @return type
     */
    static public function encode_content_links($content) {
        global $CFG;
        $base = preg_quote($CFG->wwwroot,"/");
        // Link to the list of swfs
        $search="/(".$base."\/mod\/swf\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SWFINDEX*$2@$', $content);
        // Link to swf view by moduleid
        $search="/(".$base."\/mod\/swf\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@SWFVIEWBYID*$2@$', $content);
        return $content;
    }
}