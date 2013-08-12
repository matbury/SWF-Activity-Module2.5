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

function swf_print_enrolled_users($context, $courseid, $userid, $sortby)
{
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
    }
    return $swf_current_user;
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
 * 
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
 * 
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
