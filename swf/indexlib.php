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

defined('MOODLE_INTERNAL') || die();

// Contains specific functions for moodle/mod/swf/index.php
/**
 * 
 * @param object $context
 * @param int $courseid
 * @param int $userid
 * @param string $sortby
 * @return object $swf_current_user
 */
function swf_print_enrolled_users($context, $courseid, $userid, $sortby)
{
    global $USER;
    // Create a list of users in course index.php?id=$courseid&userid=$userid&sortby=lastname
    if($swf_enrolled_users = get_enrolled_users($context, '', 0, 'u.*', $sortby))
    { // Sort users by last name
        echo '<p>'.get_string('viewreport', 'swf').': ';
        foreach($swf_enrolled_users as $swf_enrolled_user)
        {
            if($swf_enrolled_user->id == $userid)
            {
                $swf_current_user = $swf_enrolled_user; // Get the user calling this page
            }
            echo '&nbsp;&nbsp;&nbsp;<a href="index.php?id='
                    .$courseid.'&userid='
                    .$swf_enrolled_user->id.'" title="'
                    .get_string('viewreport', 'swf').' '
                    .$swf_enrolled_user->firstname.' '
                    .$swf_enrolled_user->lastname.'">'
                    .$swf_enrolled_user->firstname.' '
                    .$swf_enrolled_user->lastname.'</a>';
        }
        if($sortby === 'lastname')
        { // Sort users by last name
            echo '<br/>'.get_string('sortby', 'swf').':&nbsp;&nbsp;&nbsp;<a href="index.php?id='
                    .$courseid.'&userid='
                    .$userid.'&sortby=firstname" title="'
                    .get_string('sortby', 'swf').' '
                    .get_string('firstname').'">'
                    .get_string('firstname').'</a>&nbsp;&nbsp;&nbsp;<strong>'
                    .get_string('lastname').'</strong></p>';
        } else { // Sort users by first name
            echo '<br/>'.get_string('sortby', 'swf').':&nbsp;&nbsp;&nbsp;<strong>'
                    .get_string('firstname').'</strong>&nbsp;&nbsp;&nbsp;<a href="index.php?id='
                    .$courseid.'&userid='
                    .$userid.'&sortby=lastname" title="'
                    .get_string('sortby', 'swf').' '
                    .get_string('lastname').'">'
                    .get_string('lastname').'</a></p>';
        }
    } else {
        $swf_current_user = $USER; // Don't return an empty object
    }
    return $swf_current_user;
}

/**
 * 
 * @param string $course_format
 * @return \html_table
 */
function swf_init_main_table($course_format)
{
    // Build headers for HTML table of instances, grades, and grade histories
    $swf_main_table = new html_table();
    if ($course_format == 'weeks')
    {
        $swf_main_table->head  = array (' ', get_string('name'));
    } else if ($course_format == 'topics')
    {
        $swf_main_table->head  = array ('#', get_string('name'));
    } else {
        $swf_main_table->head  = array (' ', get_string('name'));
    }
    $swf_main_table->align = array ('center', '', 'center', 'center', 'center', '');
    array_push($swf_main_table->head, get_string('finalgrade', 'swf'));
    $swf_main_table->size[2] = '104px'; // Set Final Grade column width
    array_push($swf_main_table->head, ''); // Passed / Not passed
    $swf_grade_history_key = '<br/><img src="pix/green.gif" width="8" height="8"/>|<img src="pix/black.gif" width="8" height="8"/> = grade|duration';
    array_push($swf_main_table->head, get_string('gradehistory', 'swf').$swf_grade_history_key);
    array_push($swf_main_table->head, get_string('feedback', 'swf'));
    $swf_main_table->size[6] = '35%'; // Limit Feedback column width
    return $swf_main_table;
}

/**
 * 
 * @global object $USER
 * @global object $DB
 * @param object $swf_instance
 * @param object $context
 * @return string
 */
function swf_print_name_column($swf_instance, $context)
{
    global $USER, $DB;
    if (!$swf_instance->visible)
    {
        //Show dimmed if the mod is hidden
        $swf_name_column = '<a class="dimmed" href="view.php?id='
                .$swf_instance->coursemodule.'" title="'
                .format_string($swf_instance->name).'">'
                .format_string($swf_instance->name).'</a>';
    } else {
        //Show normal if the mod is visible
        $swf_name_column = '<a href="view.php?id='
                .$swf_instance->coursemodule.'" title="'
                .format_string($swf_instance->name).'">'
                .format_string($swf_instance->name).'</a>';
    }
    
    // Show last time and user modified to admins and teachers
    if(has_capability('mod/swf:addinstance', $context, $USER->id, false))
    {
        $swf_name_column .= '<br/>('.get_string('usermodified', 'swf').': ';
        if($swf_user_modified = $DB->get_record('user', array('id' => $swf_instance->usermodified)))
        {
            $swf_name_column .= '<a href="../../user/profile.php?id='
                    .$swf_user_modified->id.'" title="'
                    .get_string('viewprofile').'">'
                    .$swf_user_modified->firstname.' '
                    .$swf_user_modified->lastname.'</a> ';
        }
        $swf_name_column .= date('Y-m-d H:i', usertime($swf_instance->timemodified)).')';
    }
    return $swf_name_column;
}

/**
 * 
 * @param object $swf_grade_record
 * @param int $userid
 * @return string
 */
function swf_print_final_grade_column($swf_grade_record, $userid)
{
    $swf_grade = round($swf_grade_record->items[0]->grades[$userid]->grade); // int
    $swf_grade_feedback = explode('|||', $swf_grade_record->items[0]->grades[$userid]->feedback);
    $swf_grade_duration = swf_seconds_to_time((int)$swf_grade_feedback[0]);
    $swf_grade_bar = '<img src="pix/green.gif" width="'.$swf_grade.'" height="6"  alt="'
            .get_string('grade', 'swf').': '.$swf_grade.'%" title="'
            .get_string('grade', 'swf').': '.$swf_grade.'%"/><img src="pix/red.gif" width="'
            .(100 - $swf_grade).'" height="6" alt="'
            .get_string('grade', 'swf').': '.$swf_grade.'%" title="'
            .get_string('grade', 'swf').': '.$swf_grade.'%"/>';
    //
    $swf_grade_duration_date_time = '<br/>'.get_string('grade', 'swf').': '.$swf_grade.'%'
            .'<br/>'.get_string('duration', 'swf').': '.$swf_grade_duration
            .'<br/>-<br/>'.date('Y-m-d', usertime($swf_grade_record->items[0]->grades[$userid]->dategraded))
            .'<br/>'.date('H:i:s', usertime($swf_grade_record->items[0]->grades[$userid]->dategraded));
    return $swf_grade_bar.$swf_grade_duration_date_time;
}

/**
 * 
 * @param object $swf_grade
 * @param int $swf_grade_pass
 * @return string 
 */
function swf_print_passed_column($swf_grade, $swf_grade_pass)
{
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
    return $swf_grade_passed.'<br/>'.$swf_grade_pass.'%';
}

/**
 * 
 * @global object $DB
 * @param array $params
 * @param int $userid
 * @return string
 */
function swf_print_grade_history_graph($params, $userid) {
    global $DB;
    $swf_grade_items = $DB->get_records('grade_items', $params);
    foreach($swf_grade_items as $swf_grade_item)
    {
        $swf_itemid = $swf_grade_item->id; // Should only be one item
    }
    $swf_grade_history_records = $DB->get_records('grade_grades_history', array('itemid'=>$swf_itemid, 'userid'=>$userid));
    $swf_grade_history_bars = '<img src="pix/black.gif" width="1" height="50"/> ';
    $swf_grade_history_total_duration = 0;
    $swf_grade_history_total_grade = 0;
    $swf_count = 0;
    foreach($swf_grade_history_records as $swf_grade_history_record)
    {
        $swf_grade_history_total_grade += (int)$swf_grade_history_record->finalgrade;
        $swf_grade_history_duration = explode('|||', $swf_grade_history_record->feedback);
        $swf_grade_history_total_duration += $swf_grade_history_duration[0];
        $swf_title = get_string('date').'/'.get_string('time', 'swf').': '
                .date('Y-m-d / H:i:s ', usertime($swf_grade_history_record->timemodified)).' | '
                .get_string('grade', 'swf').': '.(int)$swf_grade_history_record->finalgrade.'%'.' | '
                .get_string('duration', 'swf').': '.swf_seconds_to_time($swf_grade_history_duration[0]);
        $swf_grade_history_bars .= '<img src="pix/green.gif" width="8" height="'
                .($swf_grade_history_record->finalgrade / 2).'" title="'.$swf_title.'"/>';
        $swf_grade_history_bars .= '<img src="pix/black.gif" width="8" height="'
                .($swf_grade_history_duration[0] / 15).'" title="'.$swf_title.'"/>';
        $swf_count++;
    }
    if($swf_count > 0)
    {
        $swf_grade_history_bars .= '<br/>'.get_string('attempts', 'swf').': '.$swf_count;
        $swf_grade_history_bars .= '<br/>'.get_string('averagegrade', 'swf').': '
                .round($swf_grade_history_total_grade / $swf_count).'%';
        $swf_grade_history_bars .= '<br/>'.get_string('averageduration', 'swf').': '
                .swf_seconds_to_time($swf_grade_history_total_duration / $swf_count);
    }
    return $swf_grade_history_bars;
}

/**
 * A utility function that converts seconds into a formatted string
 * @param int $seconds
 * @return string - seconds in h:mm:ss format
 */
function swf_seconds_to_time($seconds)
{
    // extract hours
    $hours = floor($seconds / (60 * 60));
    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);
    if($minutes < 10) {
        $minutes = (string) '0'.$minutes;
    }
    // extract the remaining seconds
    $divisor_for_seconds = $divisor_for_minutes % 60;
    $seconds = ceil($divisor_for_seconds);
    if($seconds < 10) {
        $seconds = (string) '0'.$seconds;
    }
    return $hours.':'.$minutes.':'.$seconds;
}

/**
 * Create a 8 x 25 table for heatmap
 * @return \html_table
 */
function swf_init_heatmap_table()
{
    $swf_heatmap_table = new html_table();
    $swf_heatmap_table->head  = array(get_string('time', 'swf'));
    $swf_heatmap_table->align  = array('center');
    for($i = 0; $i < 24; $i++)
    {
        if($i < 10)
        {
            $h = (string) '0'.$i;
        } else {
            $h = (string) $i;
        }
        array_push($swf_heatmap_table->head, $h); // Column headers (hours)
        array_push($swf_heatmap_table->align, 'center'); // Align columns
    }
    
    return $swf_heatmap_table;
}

/**
 * Create a 7 days x 24 hours array to populate with grade history attempts
 * @return array $swf_heatmap_weekdays_history
 */
function swf_init_heatmap_weekdays_history()
{
    $swf_weekdays = array(  get_string('sunday', 'swf'),
                            get_string('monday', 'swf'),
                            get_string('tuesday', 'swf'),
                            get_string('wednesday', 'swf'),
                            get_string('thursday', 'swf'),
                            get_string('friday', 'swf'),
                            get_string('saturday', 'swf'));
    // Create 2D array of 7 days x 24 hours
    for($i = 1; $i < 8; $i++)
    {
        $swf_heatmap_weekdays_history[$i] = array($swf_weekdays[$i - 1]);
        for($j = 1; $j < 25; $j++)
        {
            $swf_heatmap_weekdays_history[$i][$j] = 0;
        }
    }
    return $swf_heatmap_weekdays_history;
}

/**
 * Pushes number of attempts into heatmap array according to day of week and time
 * @param array $swf_heatmap_weekdays_history
 * @param int $userid
 * @param int $swf_itemid
 * @return array $swf_heatmap_weekdays_history
 */
function swf_weekdays_history_push_item_grades($swf_heatmap_weekdays_history, $userid, $swf_itemid)
{
    global $DB;
    $swf_grade_history_records = $DB->get_records('grade_grades_history', array('itemid'=>$swf_itemid, 'userid'=>$userid));
    foreach($swf_grade_history_records as $swf_grade_history_record)
    {
        $day = date('w', usertime($swf_grade_history_record->timemodified)); // Set the day to user's time zone
        $hour = date('G', usertime($swf_grade_history_record->timemodified)); // Set the time to user's time zone
        $swf_heatmap_weekdays_history[$day + 1][$hour + 1] += 1; // Increment attempts count for each cell
    }
    return $swf_heatmap_weekdays_history;
}

/**
 * Create an array of number of attempts for each cell of a 24 x 7 grid
 * @global object $DB
 * @param \html_table $swf_heatmap_table
 * @return \html_table $swf_heatmap_table
 */
function swf_print_heatmap_table($swf_heatmap_table, $swf_heatmap_weekdays_history)
{
    global $DB;
    // Creat table rows (24 x 7 grid)
    for($k = 1; $k < 8; $k++) // 7 days (rows)
    { 
        for($l = 1; $l < 25; $l++) // 24 hours (columns)
        { 
            if($swf_heatmap_weekdays_history[$k][$l] === 0)
            {
                $swf_heatmap_weekdays_history[$k][$l] = ''; // Leave table cell blank
            } else {
                // Resize graphic according to number of attempts for cell
                $attempts = $swf_heatmap_weekdays_history[$k][$l];
                $swf_block_size = $attempts + 4; // minimum size = 5 x 5px
                $swf_heatmap_weekdays_history[$k][$l] = '<img src="pix/red.gif" width="'
                        .$swf_block_size.'" height="'
                        .$swf_block_size.'" title="x '
                        .$attempts.'"/>';
            }
        }
        $swf_heatmap_table->data[] = $swf_heatmap_weekdays_history[$k];// Push table row
    }
    return $swf_heatmap_table;
}
