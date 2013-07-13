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
require_capability('mod/swf:view', $context); // Can use view this page?

add_to_log($course->id, 'swf', 'view all', "index.php?id={$course->id}", get_string('report','swf'));

// Print header
$PAGE->set_url('/mod/swf/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

// Print enrolled users and filtering options for teachers
if(has_capability('mod/swf:viewall', $context))
{
    // Create a list of users in course index.php?id=$courseid&userid=$userid&sortby=lastname
    if($swf_enrolled_users = get_enrolled_users($context,'',0,'u.*',$sortby))
    { // Sort users by last name
        echo '<p>'.get_string('viewreport','swf').': ';
        foreach($swf_enrolled_users as $swf_enrolled_user)
        {
            if($swf_enrolled_user->id == $userid)
            {
                $swf_current_user = $swf_enrolled_user; // Get the user calling this page
            }
            echo '&nbsp;&nbsp;&nbsp;<a href="index.php?id='
                    .$course->id.'&userid='
                    .$swf_enrolled_user->id.'" title="'
                    .get_string('viewreport','swf').' '
                    .$swf_enrolled_user->firstname.' '
                    .$swf_enrolled_user->lastname.'">'
                    .$swf_enrolled_user->firstname.' '
                    .$swf_enrolled_user->lastname.'</a>';
        }
        if($sortby === 'lastname')
        { // Sort users by last name
            echo '<br/>'.get_string('sortedby','swf').':&nbsp;&nbsp;&nbsp;<a href="index.php?id='
                    .$course->id.'&userid='
                    .$userid.'&sortby=firstname" title="'
                    .get_string('sortby','swf').' '
                    .get_string('firstname').'">'
                    .get_string('firstname').'</a>&nbsp;&nbsp;&nbsp;<strong>'
                    .get_string('lastname').'</strong></p>';
        } else { // Sort users by first name
            echo '<br/>'.get_string('sortedby','swf').':&nbsp;&nbsp;&nbsp;<strong>'
                    .get_string('firstname').'</strong>&nbsp;&nbsp;&nbsp;<a href="index.php?id='
                    .$course->id.'&userid='
                    .$userid.'&sortby=lastname" title="'
                    .get_string('sortby','swf').' '
                    .get_string('lastname').'">'
                    .get_string('lastname').'</a></p>';
        }
    } else {
        // no enrolled users
    }
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
    $table->head  = array (' ',get_string('name'));
}
$table->align = array ('center', 'center', 'center', 'center', 'center', 'center');
array_push($table->head,get_string('finalgrade', 'swf'));
$table->size[2] = '104px'; // Set Final Grade column width
array_push($table->head, '');
array_push($table->head,get_string('durationhistory', 'swf'));
array_push($table->head,get_string('gradehistory', 'swf'));
array_push($table->head,get_string('feedback', 'swf'));
$table->size[6] = '35%'; // Limit Feedback column width

// Build headers for heatmap table part 1 of 3
$swf_map_table = new html_table();
$swf_map_table->head  = array (get_string('time', 'swf'));
$swf_map_table->align  = array ('center');
for($i = 0; $i < 24; $i++)
{
    if($i < 10)
    {
        $h = (string) '0'.$i;
    } else {
        $h = (string) $i;
    }
    array_push($swf_map_table->head, $h); // Column headers (hours)
    array_push($swf_map_table->align, 'center'); // Align columns
}
$swf_weekdays = array(  get_string('sunday', 'swf'),
                        get_string('monday', 'swf'),
                        get_string('tuesday', 'swf'),
                        get_string('wednesday', 'swf'),
                        get_string('thursday', 'swf'),
                        get_string('friday', 'swf'),
                        get_string('saturday', 'swf')); // Days of the week
// Create 2D array of 7 days x 24 hours
for($i = 1; $i < 8; $i++)
{
    $swf_weekdays_history[$i] = array($swf_weekdays[$i - 1]);
    for($j = 1; $j < 25; $j++)
    {
        $swf_weekdays_history[$i][$j] = 0;
    }
}// End of part 1 of 3 of heatmap

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
    if(has_capability('mod/swf:addinstance',$context,$USER->id,false)) // Show editing info to editing teachers, admins, etc.
    {
        $link .= '<br/>('.get_string('usermodified','swf').' '
                .$USER->firstname.' '
                .$USER->lastname.' '
                .date('H:i Y-m-d',usertime($swf_instance->timemodified)).')';
    }
    if ($course->format == 'weeks' || $course->format == 'topics')
    {
        $swf_table_row = array($swf_instance->section, $link);
    } else {
        $swf_table_row = array ('',$link);
    }
    // Final grade ---------------------------------------------------------- //
    $swf_grade_record = grade_get_grades($course->id,'mod','swf',$swf_instance->id,$userid);
    if($swf_grade_record->items[0]->grades[$userid]->grade)
    { // Only print table row if grade has been pushed by user
        $swf_grade = round($swf_grade_record->items[0]->grades[$userid]->grade); // int
        $swf_grade_pass = round($swf_grade_record->items[0]->gradepass); // int
        $swf_grade_feedback = explode('|||',$swf_grade_record->items[0]->grades[$userid]->feedback); // array
        $swf_grade_bar = '<img src="pix/green.gif" width="'
                .$swf_grade.'" height="6"  alt="'
                .get_string('grade', 'swf').': '
                .$swf_grade.'%" title="'
                .get_string('grade', 'swf').': '
                .$swf_grade.'%"/><img src="pix/red.gif" width="'
                .(100 - $swf_grade).'" height="6" alt="'
                .get_string('grade', 'swf').': '
                .$swf_grade.'%" title="'
                .get_string('grade', 'swf').': '
                .$swf_grade.'%"/>';
        if($swf_grade >= $swf_grade_pass)
        { // Is the final grade a pass?
            $swf_grade_passed = '<img src="pix/tick.png" alt="'
                    .get_string('passed', 'swf').'" title="'
                    .get_string('passed', 'swf').'"/>';
        } else {
            $swf_grade_passed = '<img src="pix/cross.png" alt="'
                    .get_string('notpassed', 'swf').'" title="'
                    .get_string('notpassed', 'swf').'"/>';
        }
        // Grade history ---------------------------------------------------- //
        // Get grade item for itemid
        $params = array('courseid'=>$COURSE->id,
                        'itemtype'=>'mod',
                        'itemmodule'=>'swf',
                        'iteminstance'=>$swf_instance->id);
        $swf_grade_items = $DB->get_records('grade_items',$params);
        foreach($swf_grade_items as $swf_grade_item)
        {
            $swf_itemid = $swf_grade_item->id; // Should only be one item
        }
        // Build bar graphs for Duration history and Grade history
        $swf_grade_history_records = $DB->get_records('grade_grades_history',array('itemid'=>$swf_itemid,'userid'=>$userid));
        $swf_grade_history_record_duration_bars = ''; // _bars are vertical bar graphs
        $swf_grade_history_record_grade_bars = '<img src="pix/black.gif" width="1" height="50"/> '; // vertical black line
        $swf_grade_history_record_total_grade = 0; // add up grades total
        $swf_grade_history_record_total_duration = 0; // add up duration total
        $swf_count = 0;
        foreach($swf_grade_history_records as $swf_grade_history_record)
        {
            $swf_grade_history_record_duration = explode('|||',$swf_grade_history_record->feedback); // separate duration from feedback
            $swf_grade_history_record_total_duration += $swf_grade_history_record_duration[0];
            $swf_s2t = secondsToTime($swf_grade_history_record_duration[0]); // convert to h:mm:ss format
            $swf_grade_history_record_duration_and_grade = get_string('duration', 'swf').': '
                    .$swf_s2t['h'].':'
                    .$swf_s2t['m'].':'
                    .$swf_s2t['s'].' '
                    .get_string('grade', 'swf').': '
                    .(int)$swf_grade_history_record->finalgrade.'%';
            $swf_grade_history_record_duration_bars.= '<img src="pix/black.gif" width="8" height="'
                    .($swf_grade_history_record_duration[0] / 15).'" title="'
                    .$swf_grade_history_record_duration_and_grade.'"/>'; // add duration bar 15 seconds = 1px
            $swf_grade_history_record_grade_bars.= '<img src="pix/green.gif" width="8" height="'
                    .($swf_grade_history_record->finalgrade / 2).'" title="'
                    .$swf_grade_history_record_duration_and_grade.'"/>'; // add grade bar 100% = 50px
            $swf_grade_history_record_total_grade += $swf_grade_history_record->finalgrade; // add finalgrade
            $swf_count++;
            // Heatmap table part 2 of 3
            $day = date('w',usertime($swf_grade_history_record->timemodified)); // Set the day to user's time zone
            $hour = date('G',usertime($swf_grade_history_record->timemodified)); // Set the time to user's time zone
            $swf_weekdays_history[$day + 1][$hour + 1] += 1; // Increase attempts count
            // End of part 2 of 3 of heatmap
        }
        // Push grades, graphs, dates, times, etc. into array for table row
        array_push($swf_table_row,$swf_grade_bar.'<br/>
            '.round($swf_grade_record->items[0]->grades[$userid]->grade).'%<br/>
            '.date('d-m-Y',usertime($swf_grade_record->items[0]->grades[$userid]->dategraded)).'<br/>
            '.date('H:i:s',usertime($swf_grade_record->items[0]->grades[$userid]->dategraded)));
        array_push($swf_table_row,$swf_grade_passed.'<br/>'
                .$swf_grade_pass.'%');
        $swf_total_duration = secondsToTime($swf_grade_history_record_total_duration);
        if($swf_count > 0)
        {
            $swf_total_duration_average = secondsToTime($swf_grade_history_record_total_duration / $swf_count);
            $swf_grade_history_average_grade = round($swf_grade_history_record_total_grade / $swf_count);
        }
        array_push($swf_table_row,$swf_grade_history_record_duration_bars.'<br/>'
                .get_string('total', 'swf').': '
                .$swf_total_duration['h'].':'
                .$swf_total_duration['m'].':'
                .$swf_total_duration['s'].'<br/>'
                .get_string('average', 'swf').': '
                .$swf_total_duration_average['h'].':'
                .$swf_total_duration_average['m'].':'
                .$swf_total_duration_average['s']);
        array_push($swf_table_row,$swf_grade_history_record_grade_bars.'<br/>'
                .get_string('average', 'swf').': '
                .$swf_grade_history_average_grade.'%'.'<br/>'
                .get_string('attempts', 'swf').': '
                .$swf_count);
        array_push($swf_table_row,$swf_grade_feedback[1]);
    } else {
        // No grade so leave table row blank
        array_push($swf_table_row,get_string('nograde', 'swf'));
        for($i = 0; $i < 4; $i++)
        {
            array_push($swf_table_row,' - '); // Put dashes in '', duration history, grade history, and feedback
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

/*
 * Heatmap table part 3 of 3
 */
echo $OUTPUT->heading(get_string('heatmap', 'swf'), 3);
// Creat table rows
for($k = 1; $k < 8; $k++)
{ // days (rows)
    for($l = 1; $l < 25; $l++)
    { // hours (columns)
        if($swf_weekdays_history[$k][$l] === 0)
        {
            $swf_weekdays_history[$k][$l] = ''; // Leave table cell blank
        } else {
            // Resize graphic according to number of attempts for cell
            $attempts = $swf_weekdays_history[$k][$l];
            $swf_block_size = $attempts + 4; // minimum size = 5px x 5px
            $swf_weekdays_history[$k][$l] = '<img src="pix/red.gif" width="'.$swf_block_size.'" height="'.$swf_block_size.'" title="x '.$attempts.'"/>';
        }
    }
    $swf_map_table->data[] = $swf_weekdays_history[$k];// Push table row
}

// Print heatmap table
echo html_writer::table($swf_map_table);// End of part 3 of 3 of heatmap

// Finish the page
echo $OUTPUT->footer();
