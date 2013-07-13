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
 * @package     mod
 * @package     swf
 * @copyright   2013 Matt Bury
 * @author      Matt Bury <matt@matbury.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/swf/lib.php');
    
    $settings->add(new admin_setting_configtext('swf_installed_apps_dir', get_string('installed_apps_dir', 'swf'), get_string('installed_apps_dir_explain', 'swf'), $CFG->dirroot.'/mod/swf/swfs', PARAM_RAW, 60));
    
    $settings->add(new admin_setting_configtext('swf_content_dir', get_string('content_dir', 'swf'), get_string('content_dir_explain', 'swf'), '/repository/swfcontent', PARAM_RAW, 60));
    
}

