<?php
/** This service handles grades for SWF Activity Module
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: Grades.php,v 0.2 2010/02/01 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* Copyright (C) 2010  Matt Bury
*
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

All methods in this service class are course-wide only. 

Method List

public methods:
	amf_ping() // returns string 'Ping!' to check if connection was successful
	amf_grade_update() // creates or updates grade in grade_grades DB table
	amf_grade_get_grades() // 
	amf_grade_get_all_swf_grades() // returns all grades for grade items of SWF Activity Module
	amf_grade_get_course_grade() // 
	
*/

class Grades
{
    private $access;
    private $users;

    public function __construct()
    {
        // Check current user's capabilities
        require_once('Access.php');
        $this->access = new Access();
        // Get user's profile details
        require_once('Users.php');
        $this->users = new Users();
    }

    /** 
    * Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
    * @return object
    */
    public function amf_ping($obj = null)
    {
        $swf_return->result = 'SUCCESS';
        $swf_return->message = 'Grades.amf_ping successfully called.';
        $swf_return->service_version = '2013.05.14';
        $swf_return->php_version = phpversion(); // get current server PHP version
        return $swf_return;
    }

    /**
    * 
    * @param obj->instance int course instance ID
    * @param obj->swfid int swf instance ID
    * @param obj->feedback int/text feedback for user either elapsed time in seconds or text
    * @param obj->feedbackformat int format of feedback. See: moodle/lib/weblib.php for definitions
    * @param obj->rawgrade int grade before calculation as a percentage, scale, etc.
    * @return array of grade information objects (scaleid, name, grade and locked status, etc.) indexed with itemnumbers
    */
    //public function amf_grade_update() // For testing in service browser
    public function amf_grade_update($obj = null)
    {
        // pass in empty object {} to test
        /* */
        $obj['userid'] = 2;
        $obj['instance'] = 111; // Course module ID, e.g. mod/swf/view.php?id=3
        $obj['swfid'] = 1; // SWF Activity Module ID
        $obj['feedback'] = 'This is some sample feedback and here\'s a link: http://blog.matbury.com/';
        $obj['feedbackformat'] = rand(29,3800); // time elapsed
        $obj['rawgrade'] = rand(0,100);
        
        // Get current user's capabilities
        $capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
        // If there was a problem with authentication, return the error message
        if(!$capabilities->is_logged_in)
        {
            return $capabilities;
        }
        
        // Make sure they have permission to call this function
        if ($capabilities->view_own_grades) {
            global $CFG;
            global $DB;
            global $USER;

            require_once($CFG->libdir.'/gradelib.php');

            // Add view to Moodle log
            add_to_log($capabilities->course, 'swf', 'amfphp grade_update swfid:'.$obj['swfid'], "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 

            // Set upper time limit of 8 hours
            // if it's more then it's probably been sent as raw milliseconds
            $swf_elapsed_seconds = (int)$obj['feedbackformat'];
            if($swf_elapsed_seconds > 28800) {
                $obj['feedbackformat'] = round($swf_elapsed_seconds / 1000);
            }

            // Create grade object to pass in grade data
            $swf_return = new stdClass();
            $grade = new stdClass();
            $grade->feedback = $obj['feedbackformat'].'|||'.$obj['feedback']; // use '|||' to split string for elapsed time
            $grade->feedbackformat = 1; // FORMAT_HTML Plain HTML (with some tags stripped)
            $grade->rawgrade = $obj['rawgrade'];
            $grade->swfid = $obj['swfid'];
            $grade->timemodified = time();
            $grade->userid = $USER->id;
            $grade->usermodified = $USER->id;

            // Get grade item for grademax and grademin values
            // If SWF mod instance gradetype is set to none, no grade item is created
            $conditions = array(iteminstance=>$grade->swfid, 'itemtype' => 'mod');
            if(!$record = $DB->get_records('grade_items',$conditions)) {
                // Send error string back to Flash client
                $swf_return->result = 'NO_GRADE_ITEM';
                $swf_return->message = get_string('grade_no_item','swf');
                return $swf_return;
            }
            // Set grade min and max values
            $grade->rawgrademax = $record->grademax;
            $grade->rawgrademin = $record->grademin;
            
            // Insert grade
            grade_update('mod/swf', $capabilities->course, 'mod', 'swf', $grade->swfid, 0, $grade, NULL);
            $swf_return->result = 'SUCCESS';
            $swf_return->message = get_string('grade_saved','swf');
            return $swf_return;
        }
        $swf_return->result = 'NO_PERMISSION';
        $swf_return->message = get_string('grade_no_permission','swf');
        return $swf_return;
    }

    /**
    * Returns grade for the specified grade_item and user
    * @param obj->instance int course instance ID
    * @param obj->swfid int swf module ID
    * @param obj->userid int (optional) specify ID of user if teacher or admin
    * @return array of grade information objects (scaleid, name, grade and locked status, etc.) indexed with itemnumbers
    */
    public function amf_grade_get_grades($obj)
    {
        // Dummy values for testing in service browser
        /* 
        $obj['instance'] = 2;
        $obj['swfid'] = 1;
        $obj['userid'] = 4;
        */
        // Get current user's capabilities
        $capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
        // If there was a problem with authentication, return the error message
        if(!empty($capabilities->error)) {
            return $capabilities->error;
        }
        // Make sure they have permission to call this function
        if ($capabilities->is_logged_in && $capabilities->view_own_grades) {
            global $CFG;
            global $USER;

            require_once($CFG->libdir.'/gradelib.php');

            // Add view to Moodle log
            add_to_log($capabilities->course, 'swf', 'amfphp grade_get_grades', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 

            // Only users with capabilties can view other users' grades
            if($capabilities->view_all_grades) {
                $userid = $obj['userid'];
            } else {
                $userid = $USER->id;
            }

            //
            try {
                $result = grade_get_grades($capabilities->course, 'mod', 'swf', $obj['swfid'], $userid);
                $swf_return->grade = $result->items[0]->grades[$grade->userid];
                $swf_return->result = 'SUCCESS';
                $swf_return->message = get_string('grade_accessed','swf');
                return $swf_return;
                return $result;
            } catch (Exception $e) {
                // probably a PHP 5.3+ deprecation warning
                return $e->getMessage();
            }
        }
        $swf_return->result = 'NO_PERMISSION';
        $swf_return->message = get_string('grade_no_permission','swf');
        return $swf_return;
    }

    /**
    * Returns grades for all the swf module instances on the current course
    * @param obj->instance int course instance ID
    * @param obj->swfid int swf module ID
    * @param obj->userid int (optional) specify ID of user if teacher or admin
    * @return array of grade information objects (scaleid, name, grade and locked status, etc.) indexed with itemnumbers
    */
    public function amf_grade_get_all_swf_grades($obj)
    {
        // Dummy values for testing in service browser
        /* 
        $obj['instance'] = 2;
        $obj['swfid'] = 1;
        $obj['userid'] = 5;
        */
        // Get current user's capabilities
        $capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
        // If there was a problem with authentication, return the error message
        if(!empty($capabilities->error)) {
            return $capabilities->error;
        }
        // Make sure they have permission to call this function
        if ($capabilities->is_logged_in && $capabilities->view_own_grades) 		{
            global $CFG;
            global $USER;

            require_once($CFG->libdir.'/gradelib.php');

            // Add view to Moodle log
            add_to_log($capabilities->course, 'swf', 'amfphp grade_get_all_swf_grades', "view.php?id=$capabilities->cmid", "$capabilities->swfname");

            // Only users with capabilties can view other users' grades
            if($capabilities->view_all_grades) {
                if($obj['userid']) {
                    $userid = $obj['userid'];
                } else {
                    $userid = $USER->id;
                }
            } else {
                $userid = $USER->id;
            }

            // Get all instances of SWFs for current course
            if ($swf_instances = get_all_instances_in_course('swf', $capabilities->courseobject)) {
                $swf_instances_grades = array();
                foreach($swf_instances as $swf_instance) {
                    // grade_get_grades($courseid, $itemtype, $itemmodule, $iteminstance, $userid_or_ids=null)
                    $swf_grade = grade_get_grades($capabilities->course, 'mod', 'swf', $swf_instance->id, $userid);
                    array_push($swf_instances_grades,$swf_grade->items[0]->grades[$userid]);
                }
                $swf_return->records = $swf_instances_grades;
                $swf_return->result = 'SUCCESS';
                $swf_return->message = get_string('grade_accessed','swf');
                return $swf_return;
            }
        }
        $swf_return->result = 'NO_PERMISSION';
        $swf_return->message = get_string('grade_no_permission','swf');
        return $swf_return;
    }


    /**
    * Returns grade for the specified grade_item and user
    * @param obj->instance int course instance ID
    * @param obj->swfid int swf module ID
    * @param obj->userid int (optional) specify ID of user if teacher or admin
    * @return array of grade information objects (scaleid, name, grade and locked status, etc.) indexed with itemnumbers
    */
    public function amf_grade_get_course_grade($obj)
    {
        // Dummy values for testing in service browser
        /* 
        $obj['instance'] = 2;
        $obj['swfid'] = 1;
        $obj['userid'] = 5;
        */
        // Get current user's capabilities
        $capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
        // If there was a problem with authentication, return the error message
        if(!empty($capabilities->error)) {
            return $capabilities->error;
        }
        // Make sure they have permission to call this function
        if ($capabilities->is_logged_in && $capabilities->view_own_grades) {
            global $CFG;
            global $USER;

            require_once($CFG->libdir.'/gradelib.php');

            // Add view to Moodle log
            add_to_log($capabilities->course, 'swf', 'amfphp amf_grade_get_course_grade', "view.php?id=$capabilities->cmid", "$capabilities->swfname");

            // Only users with capabilties can view other users' grades
            if($capabilities->view_all_grades) {
                if($obj['userid']) {
                    $userid = $obj['userid'];
                } else {
                    $userid = $USER->id;
                }
            } else {
                $userid = $USER->id;
            }

            // Get all instances of SWFs for current course
            if ($swf_instances = get_all_instances_in_course('swf', $capabilities->courseobject)) {

                $swf_instances_grades = array();

                foreach($swf_instances as $swf_instance) {
                    // grade_get_grades($courseid, $itemtype, $itemmodule, $iteminstance, $userid_or_ids=null)
                    $swf_grade = grade_get_grades($capabilities->course, 'mod', 'swf', $swf_instance->id, $userid);
                    array_push($swf_instances_grades,$swf_grade->items[0]->grades[$userid]);
                }
                $swf_return->records = $swf_instances_grades;
                $swf_return->result = 'SUCCESS';
                $swf_return->message = get_string('grade_accessed','swf');
                return $swf_return;
            }
        }
        $swf_return->result = 'NO_PERMISSION';
        $swf_return->message = get_string('grade_no_permission','swf');
        return $swf_return;
    }

    /**
    * Clean up objects and variables for garbage collector
    *
    */
    public function __destruct()
    {
        unset($access);
    }
}
// No, I haven't forgotten the closing PHP tag. It's not necessary here.