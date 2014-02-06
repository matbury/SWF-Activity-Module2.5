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

require_once('../../config.php');
require_once('../../lib/filelib.php'); // for mimeinfo() and send_file()

require_login(); // Users must be logged in to access files

global $CFG;
    
// (Optional) Clean the SWF content directory setting.
$CFG->swf_content_dir = clean_param($CFG->swf_content_dir, PARAM_PATH);
// Remove trailing slash(es).
$CFG->swf_content_dir = rtrim($CFG->swf_content_dir, '/');
// Get the relative path of the requested content.
$swf_relative_path = get_file_argument();
if (empty($CFG->swf_content_dir) || !$swf_relative_path) {
    header('HTTP/1.0 404 Not Found');
    exit(get_string('content_error','swf'));
} else if ($swf_relative_path{0} != '/') {
    // Relative path must start with '/'.
    header('HTTP/1.0 404 Not Found');
    exit(get_string('content_error','swf'));
}

$swf_data_path = realpath($CFG->dataroot . $CFG->swf_content_dir . $swf_relative_path);
// (Paranoid) Content will be served just from the SWF content dir.
if (strpos($swf_data_path, realpath($CFG->dataroot . $CFG->swf_content_dir)) === 0) {
    $swf_data_info = pathinfo($swf_data_path);
    $swf_mime_type = mimeinfo('type', $swf_data_info['basename']);
    send_file($swf_data_path, $swf_data_info['basename'], 'default', 0, false, false, $swf_mime_type, false);
} else {
    header('HTTP/1.0 404 Not Found');
    exit(get_string('content_error','swf'));
}
