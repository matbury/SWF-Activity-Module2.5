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
require_once('../../lib/gradelib.php');
require_once('indexlib.php');

$id = required_param('id', PARAM_INT);   // Course
$userid = optional_param('userid', $USER->id, PARAM_INT); // User
$sortby = optional_param('sortby', 'lastname', PARAM_TAG); // Sort order for names list

if(! $course = $DB->get_record('course', array('id' => $id)))
{
    error('Course ID is incorrect');
}

require_course_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('mod/swf:view', $context);

add_to_log($course->id, 'swf', 'view all', "index.php?id={$course->id}", get_string('report', 'swf'));

// Print header
$PAGE->set_url('/mod/swf/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
echo $OUTPUT->header();

// Print enrolled users and filtering options for teachers
if(has_capability('mod/swf:viewall', $context))
{
    // Create a list of users in course index.php?id=$courseid&userid=$userid&sortby=lastname
    $swf_current_user = swf_print_enrolled_users($context, $course->id, $userid, $sortby);
} else {
    $userid = $USER->id; // Don't allow them to see others' reports
    $swf_current_user = $USER; // Current user is calling this page
}

// Get all swf instances in course
if (! $swfs = get_all_instances_in_course('swf', $course))
{
    echo $OUTPUT->heading(get_string('noswfs', 'swf'), 2);
    echo $OUTPUT->continue_button('../../course/view.php?id='.$course->id);
    echo $OUTPUT->footer();
    exit;
} // No SWFs so end of output

$swf_main_table = swf_init_main_table($course->format);
$swf_itemid = 0; // Initialise this in case there are no grades
$swf_heatmap_table = swf_init_heatmap_table();
$swf_heatmap_weekdays_history = swf_init_heatmap_weekdays_history(); // Stores attempts for heatmap table

// Iterate through all instances in course and print out info
foreach($swfs as $swf_instance)
{
    // Number / Topic column
    if ($course->format == 'weeks' || $course->format == 'topics')
    {
        $swf_table_row = array($swf_instance->section);
    } else {
        $swf_table_row = array ('');
    }
    
    // Name column
    array_push($swf_table_row, swf_print_name_column($swf_instance, $context));
    
    // Grades
    $swf_grade_record = grade_get_grades($course->id, 'mod', 'swf', $swf_instance->id, $userid);
    if($swf_grade_record->items[0]->grades[$userid]->grade)
    {
        // Get grade item for itemid
        $params = array('courseid'=>$COURSE->id,
                        'itemtype'=>'mod',
                        'itemmodule'=>'swf',
                        'iteminstance'=>$swf_instance->id);
        $swf_grade_items = $DB->get_records('grade_items', $params);
        foreach($swf_grade_items as $swf_grade_item)
        {
            $swf_itemid = $swf_grade_item->id; // Should only be one item
        }
        
        // Final grade column
        array_push($swf_table_row, swf_print_final_grade_column($swf_grade_record, $userid));
        
        // Passed / Not Passed column
        array_push($swf_table_row, swf_print_passed_column(
                round($swf_grade_record->items[0]->grades[$userid]->grade),
                round($swf_grade_record->items[0]->gradepass)));
        
        // Grade history graph column
        array_push($swf_table_row, swf_print_grade_history_graph($params, $userid));
        
        // Feedback column
        $swf_grade_feedback = explode('|||',$swf_grade_record->items[0]->grades[$userid]->feedback);
        array_push($swf_table_row, $swf_grade_feedback[1]);
        
        // Push grade history attempts into heatmap table array
        $swf_heatmap_weekdays_history = swf_weekdays_history_push_item_grades($swf_heatmap_weekdays_history, $userid, $swf_itemid);
    } else {
        // No grade so leave table row blank
        array_push($swf_table_row, get_string('nograde', 'swf'));
        for($i = 0; $i < 3; $i++)
        {
            array_push($swf_table_row, ' - ');
        }
    }
    $swf_main_table->data[] = $swf_table_row;
}

echo $OUTPUT->heading($swf_current_user->firstname.' '.$swf_current_user->lastname, 2);
echo $OUTPUT->heading(get_string('report', 'swf'), 3);

// Tell user if grade history has been disabled
if($CFG->disablegradehistory == 1)
{
    echo $OUTPUT->heading(get_string('disablegradehistory', 'swf'), 3);
}
echo html_writer::table($swf_main_table);

// Print out user's activity heatmap
echo $OUTPUT->heading(get_string('heatmap', 'swf'), 3);
echo html_writer::table(swf_print_heatmap_table($swf_heatmap_table, $swf_heatmap_weekdays_history));

// Finish the page
echo $OUTPUT->footer();
