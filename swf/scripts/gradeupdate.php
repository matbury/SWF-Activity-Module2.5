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

require_once('../../../config.php');
require_once($CFG->libdir.'/gradelib.php');

$id = required_param('instance', PARAM_INT);
$n = required_param('swfid', PARAM_INT);
$swf_rawgrade = optional_param('rawgrade', 0, PARAM_INT);
$swf_elapsed_time = optional_param('feedbackformat', 0, PARAM_RAW);
$swf_feedback = optional_param('feedback', 0, PARAM_RAW);

if ($id) {
    $cm         = get_coursemodule_from_id('swf', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $swf  = $DB->get_record('swf', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $swf  = $DB->get_record('swf', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $swf->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('swf', $swf->id, $course->id, false, MUST_EXIST);
} else {
    echo get_string('grade_update_failed','swf').' '.get_string('grade_update_no_id','swf');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/swf:submit', $context);

add_to_log($course->id, 'swf', 'submit', "view.php?id={$cm->id}", $swf->name, $cm->id);

if($swf_rawgrade === 0)
{
    echo get_string('grade_update_failed','swf').' '.get_string('grade_update_no_grade','swf');
    die;
}

if($swf_feedback === 0 || $swf_feedback === '' || $swf_feedback === NULL)
{
    $swf_feedback = get_string('grade_update_no_feedback','swf');
}

// Elapsed time can't be more than Moodle's session timeout
// If it's more then it's probably been sent as milliseconds
if($swf_elapsed_time > $CFG->sessiontimeout) {
    $swf_elapsed_time = round($swf_elapsed_time / 1000);
}

// Create grade object to pass in grade data
$swf_return = new stdClass();
$grade = new stdClass();
$grade->feedback = $swf_elapsed_time.'|||'.$swf_feedback; // use '|||' to split string for elapsed time
$grade->feedbackformat = FORMAT_HTML; // FORMAT_HTML = 1 Plain HTML (with some tags stripped)
$grade->rawgrade = $swf_rawgrade;
$grade->swfid = $n;
$grade->timemodified = time();
$grade->userid = $USER->id;
$grade->usermodified = $USER->id;

// Get grade item for grademax and grademin values
// If SWF mod instance gradetype is set to none, no grade item is created
$conditions = array('iteminstance' => $grade->swfid, 'itemtype' => 'mod', 'itemmodule' => 'swf');
if(!$record = $DB->get_record('grade_items', $conditions)) {
    echo get_string('grade_update_no_item', 'swf');
    exit;
}
// Set grade min and max values
$grade->rawgrademax = $record->grademax;
$grade->rawgrademin = $record->grademin;

// Insert grade
// @return int Returns GRADE_UPDATE_OK = 0, GRADE_UPDATE_FAILED = 1, GRADE_UPDATE_MULTIPLE = 2, or GRADE_UPDATE_ITEM_LOCKED = 4
$grade_update_return = grade_update('mod/swf', $course->id, 'mod', 'swf', $grade->swfid, 0, $grade, NULL);
switch($grade_update_return)
{
    case GRADE_UPDATE_OK:
        echo get_string('grade_update_ok','swf');
        break;
    case GRADE_UPDATE_FAILED:
        echo get_string('grade_update_failed','swf');
        break;
    case GRADE_UPDATE_MULTIPLE:
        echo get_string('grade_update_multiple','swf');
        break;
    case GRADE_UPDATE_ITEM_LOCKED:
        echo get_string('grade_update_item_locked','swf');
        break;
    default:
        echo get_string('grade_update_failed','swf');
}