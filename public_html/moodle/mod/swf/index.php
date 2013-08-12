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

// Build headers for HTML table of instances, grades, and grade histories
$table = new html_table();
if ($course->format == 'weeks')
{
    $table->head  = array (' ', get_string('name'));
} else if ($course->format == 'topics')
{
    $table->head  = array ('#', get_string('name'));
} else {
    $table->head  = array (' ', get_string('name'));
}
$table->align = array ('center', '', 'center', 'center', 'center', '');
array_push($table->head, get_string('finalgrade', 'swf'));
$table->size[2] = '104px'; // Set Final Grade column width
array_push($table->head, '');
array_push($table->head, get_string('gradehistory', 'swf'));
array_push($table->head, get_string('feedback', 'swf'));
$table->size[6] = '35%'; // Limit Feedback column width

$swf_itemid = 0; // Initialise this in case there are no grades
$swf_heatmap_table = swf_init_heatmap_table();
$swf_heatmap_weekdays_history = swf_init_heatmap_weekdays_history(); // Stores attempts for heatmap table

// Iterate through all instances in course and print out info
foreach($swfs as $swf_instance)
{
    if (!$swf_instance->visible)
    {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='
                .$swf_instance->coursemodule.'" title="'
                .format_string($swf_instance->name).'">'
                .format_string($swf_instance->name).'</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id='
                .$swf_instance->coursemodule.'" title="'
                .format_string($swf_instance->name).'">'
                .format_string($swf_instance->name).'</a>';
    }
    
    // Show last time and user modified to admins and teachers
    if(has_capability('mod/swf:addinstance', $context, $USER->id, false))
    {
        $link .= '<br/>('.get_string('usermodified', 'swf').': ';
        if($swf_user_modified = $DB->get_record('user', array('id' => $swf_instance->usermodified)))
        {
            $link .= '<a href="../../user/profile.php?id='
                    .$swf_user_modified->id.'" title="'
                    .get_string('viewprofile').'">'
                    .$swf_user_modified->firstname.' '
                    .$swf_user_modified->lastname.'</a> ';
        }
        $link .= date('Y-m-d H:i', usertime($swf_instance->timemodified)).')';
    }
    
    // Number and Name columns
    if ($course->format == 'weeks' || $course->format == 'topics')
    {
        $swf_table_row = array($swf_instance->section, $link);
    } else {
        $swf_table_row = array ('', $link);
    }
    
    // Final grade ---------------------------------------------------------- //
    $swf_grade_record = grade_get_grades($course->id, 'mod', 'swf', $swf_instance->id, $userid);
    if($swf_grade_record->items[0]->grades[$userid]->grade)
    {
        // Grade history ---------------------------------------------------- //
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
        
        $swf_grade = round($swf_grade_record->items[0]->grades[$userid]->grade); // int
        $swf_grade_pass = round($swf_grade_record->items[0]->gradepass); // int
        $swf_grade_feedback = explode('|||',$swf_grade_record->items[0]->grades[$userid]->feedback); // array
        $swf_grade_bar = '<img src="pix/green.gif" width="'.$swf_grade.'" height="6"  alt="'
                .get_string('grade', 'swf').': '.$swf_grade.'%" title="'
                .get_string('grade', 'swf').': '.$swf_grade.'%"/><img src="pix/red.gif" width="'
                .(100 - $swf_grade).'" height="6" alt="'
                .get_string('grade', 'swf').': '.$swf_grade.'%" title="'
                .get_string('grade', 'swf').': '.$swf_grade.'%"/>';
        if($swf_grade >= $swf_grade_pass)
        {
            $swf_grade_passed = '<img src="pix/tick.png" alt="'
                    .get_string('passed', 'swf').'" title="'
                    .get_string('passed', 'swf').'"/>';
        } else {
            $swf_grade_passed = '<img src="pix/cross.png" alt="'
                    .get_string('notpassed', 'swf').'" title="'
                    .get_string('notpassed', 'swf').'"/>';
        }
        // Final grade column
        array_push($swf_table_row, $swf_grade_bar.'<br/>
            '.round($swf_grade_record->items[0]->grades[$userid]->grade).'%<br/>
            '.date('Y-m-d', usertime($swf_grade_record->items[0]->grades[$userid]->dategraded)).'<br/>
            '.date('H:i:s', usertime($swf_grade_record->items[0]->grades[$userid]->dategraded)));
        // Passed / Not Passed column
        array_push($swf_table_row, $swf_grade_passed.'<br/>'.$swf_grade_pass.'%');
        // Grade history graph column
        $swf_grade_history_graph = swf_print_grade_history_graph($params, $userid);
        array_push($swf_table_row, $swf_grade_history_graph);
        // Feedback column
        array_push($swf_table_row, $swf_grade_feedback[1]);
        
        // Push grade history attempts into heatmap table array
        $swf_heatmap_weekdays_history = swf_weekdays_history_push_item_grades($swf_heatmap_weekdays_history, $userid, $swf_itemid);
        
    } else {
        // No grade so leave table row blank
        array_push($swf_table_row, get_string('nograde', 'swf'));
        for($i = 0; $i < 3; $i++)
        {
            array_push($swf_table_row, ' - '); // Put dashes in '', duration history, grade history, and feedback
        }
    }
    $table->data[] = $swf_table_row;
}

echo $OUTPUT->heading($swf_current_user->firstname.' '.$swf_current_user->lastname, 2);
echo $OUTPUT->heading(get_string('report', 'swf'), 3);
// Tell user if grade history has been disabled
if($CFG->disablegradehistory == 1)
{
    echo $OUTPUT->heading(get_string('disablegradehistory', 'swf'), 3);
}
echo html_writer::table($table);

// Print out user's activity heatmap
echo $OUTPUT->heading(get_string('heatmap', 'swf'), 3);
$swf_heatmap_table = swf_print_heatmap_table($swf_heatmap_table, $swf_heatmap_weekdays_history);
echo html_writer::table($swf_heatmap_table);

// Finish the page
echo $OUTPUT->footer();
