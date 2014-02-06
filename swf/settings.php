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
 * @package     mod_swf
 * @copyright   2013 Matt Bury
 * @author      Matt Bury <matt@matbury.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/swf/lib.php');
    
    $swf_width = 90;
    
    // Move swf directory?
    $swf_data_dir = '';
    if(isset($CFG->swf_data_dir)) {
        $swf_data_dir = swf_rename_swfdir();
    }
    
    $settings->add(new admin_setting_configtext('swf_data_dir', get_string('data_dir', 'swf'), get_string('data_dir_explain', 'swf').' '.$swf_data_dir, $CFG->dataroot.'/repository/swf/', PARAM_RAW, $swf_width));
    
    $settings->add(new admin_setting_configtext('swf_data_url', get_string('data_url', 'swf'), get_string('data_url_explain', 'swf'), $CFG->wwwroot.'/mod/swf/content.php/', PARAM_RAW, $swf_width));
    
    $settings->add(new admin_setting_configtext('swf_data_structure', get_string('data_structure', 'swf'), get_string('data_structure_explain', 'swf'), '*/*/xml/*.*', PARAM_RAW, $swf_width));
    
}

