<?php
/** This service handles messages for SWF Activity Module
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: Snapshot.php,v 1.0 2012/10/21 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* Copyright (C) 2011  Matt Bury
*
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Snapshot
{
    private $access;
    private $users;
    private $grades;

    public function __construct()
    {
        // Check current user's capabilities
        require_once('Access.php');
        $this->access = new Access();
        // Get user's profile details
        require_once('Users.php');
        $this->users = new Users();
        // Push grade into grade book
        require_once('Grades.php');
        $this->grades = new Grades();
    }

    // ------------------------------------------------------------ PUBLIC FUNCTION ------------------------------------------------------------ //
    /** Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
    * 
    * @return string
    */
    public function amf_ping($obj = null)
    {
        $swf_return->result = 'SUCCESS';
        $swf_return->message = 'Snapshot.amf_ping successfully called.';
        $swf_return->service_version = '2012.11.18';
        $swf_return->php_version = phpversion(); // get current server PHP version
        return $swf_return;
    }

    /**
    * Receives and saves a PNG or JPG image sent from Flash app as a ByteArray
    *
    * @param instance (int) - Moodle module instance
    * @param swfid (int) - SWF Activity Module instance
    * @param bytearray (ByteArray) - PNG or JPG image
    * 
    * @return (string) - either URL to saved image or error message
    */
    public function amf_save_snapshot($obj)
    {
        /* 
        // To authenticate the AMFPHP browser app in Moodle, take at least one of
        // the following values from an existing instance of the SWF Activity Module
        $obj['instance'] = 0;
        $obj['swfid'] = 0;
        */
        // Get current user's capabilities
        $capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
        // If there was a problem with authentication, return the error message
        if(!empty($capabilities->error)) {
            return $capabilities->error;
        }
        // Make sure they have permission to call this function
        if ($capabilities->view_own_grades) {
            global $CFG;
            global $USER;

            // Add view to Moodle log
            add_to_log($capabilities->course, 'swf', 'amfphp amf_save_snapshot', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 

            // Strip out spaces and special characters
            $needles = array(' ','!','"','£','$','%','^','&','*','(',')','+','{','}',':',';','@','~','#','?','/','<','>','.','|','=','\'');
            $swf_dirname = str_replace($needles,'_',$capabilities->swfname); // directory name e.g. Concept_map_activity
            
            // Uncomment/Comment out the appropriate lines if you want to keep previous versions of files
            // Always same file name e.g. Smith_John.jpg so gets overwritten
            $swf_filename = str_replace($needles,'_',$USER->lastname.'_'.$USER->firstname).'.'.$obj['imagetype']; 
            // The next line doesn't overwrite previously saved images
            //$swf_filename = str_replace($needles,'_',$USER->lastname.'_'.$USER->firstname).'_'.date('Y\_M\_dS\_h\-i\-s').'.'.$obj['imagetype']; // 
            
            // Build paths to write and serve images to/from moodledata directories
            $dataroot_path = $CFG->dataroot.'/'.$CFG->swf_content_dir.'/'.$CFG->swf_saved_files_dir.'/'.$capabilities->course.'/'.$capabilities->cmid.'_'.$swf_dirname.'/';
            $moodledata_path = $CFG->wwwroot.'/mod/swf/content.php'.$CFG->swf_saved_files_dir.'/'.$capabilities->course.'/'.$capabilities->cmid.'_'.$swf_dirname.'/';

            // If necessary, create directory with SWF Activity Module instance name,
            // e.g. /home/sites/example.com/moodledata/repository/swfcontent/userfiles/[courseid]/123_Concept_map_activity/
            try {
                if(!file_exists($dataroot_path)) {
                    mkdir($dataroot_path, 0777, true);
                }
            } catch(Exception $e) {
                $swf_return->result = 'FILE_NOT_WRITTEN';
                $swf_return->imageurl = $dataroot_path;
                $swf_return->message = ''.$e->getMessage();
                return $swf_return;
            }

            // Can we write to this directory?
            if(!is_writeable($dataroot_path)) {
                $swf_return->result = 'FILE_NOT_WRITTEN';
                $swf_return->imageurl = $dataroot_path;
                $swf_return->message = $dataroot_path.' '.get_string('img_not_writeable','swf');
                return $swf_return;
            }

            // If previous file by user exists, delete it - Does not affect files with date + time in file name
            if(file_exists($dataroot_path.$swf_filename)) {
                if(!unlink($dataroot_path.$swf_filename)) {
                    $swf_return->result = 'FILE_NOT_WRITTEN';
                    $swf_return->imageurl = $dataroot_path;
                    $swf_return->message = $dataroot_path.' '.get_string('img_not_writeable','swf');
                    return $swf_return;
                }
            }

            // Save it
            $result = file_put_contents($dataroot_path.$swf_filename, $obj['bytearray']->data);
            if($result) {
                $moodledata_path_filename_time = $moodledata_path.$swf_filename.'?'.time();
                $obj['feedback'] = '<p><a href="'.$moodledata_path_filename_time.'" target="_blank"><img src="'.$moodledata_path_filename_time.'" name="'.$swf_filename.'" title="'.$swf_filename.'" alt="saved image" width="200" height="100" border="0" /></a></p>';
                
                // Push grade into Moodle grade book
                if($obj['pushgrade']) {
                    $swf_return->grade = $this->grades->amf_grade_update($obj);
                }
                $swf_return->result = 'SUCCESS';
                $swf_return->imageurl = $moodledata_path_filename_time;
                $swf_return->message = get_string('img_saved','swf');
                return $swf_return;
            } else {
                $swf_return->result = 'FILE_NOT_WRITTEN';
                $swf_return->imageurl = $moodledata_path_filename_time;
                $swf_return->message = get_string('img_not_saved','swf');
                return $swf_return;
            }
        }
        $swf_return->result = 'NO_PERMISSION';
        $swf_return->imageurl = '';
        $swf_return->message = get_string('img_no_permission','swf');
        return $swf_return;
    }

    /**
    * Receives and saves a PNG or JPG image sent from Flash app as a ByteArray
    *
    * @param instance (int) - Moodle module instance
    * @param swfid (int) - SWF Activity Module instance
    * @param bytearray (ByteArray) - PNG or JPG image
    * 
    * @return (string) - either URL to saved image or error message
    */
    public function amf_save_avatar($obj)
    {
        /* 
        // To authenticate the AMFPHP browser app in Moodle, take at least one of
        // the following values from an existing instance of the SWF Activity Module
        $obj['instance'] = 244;
        $obj['swfid'] = 53;
        */
        // Get current user's capabilities
        $capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
        // If there was a problem with authentication, return the error message
        if(!empty($capabilities->error))
        {
        return $capabilities->error;
        }
        // Make sure they have permission to call this function
        if ($capabilities->is_logged_in && $capabilities->view_own_grades)
        {
        global $CFG;
        global $USER;

        // Add view to Moodle log
        add_to_log($capabilities->course, 'swf', 'amfphp amf_save_avatar', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 

        //if($CFG->dataroot == $CFG->swf_dataroot)
        //{
        // Build path to user's avatar files directory
        $dataroot_path = $CFG->dataroot.'/user/0/'.$USER->id.'/';
        $user_path = $CFG->wwwroot.'/user/pix.php/'.$USER->id.'/';
        //} else
        //{
        // Build alternative path to user's avatar files directory
        //$dataroot_path = $CFG->swf_dataroot.'/user/0/'.$USER->id.'/';
        //$user_path = $CFG->swf_wwwroot.'/user/'.$USER->id.'/';
        //}

        // If necessary, create directory with SWF Activity Module instance name,
        // e.g. /home/sites/example.com/moodledata/99/snapshots/Concept_map_activity/
        try {
        if(!file_exists($dataroot_path))
        {
        mkdir($dataroot_path, 0777, true);
        }
        } catch(Exception $e)
        {
        $swf_return->result = 'FILE_NOT_WRITTEN';
        $swf_return->imageurl = $dataroot_path;
        $swf_return->message = ''.$e->getMessage();
        return $swf_return;
        }

        // If previous file by user exists, delete it - Does not affect files with date + time in file name
        if(file_exists($dataroot_path.'f1.jpg'))
        {
        if(!unlink($dataroot_path.'f1.jpg'))
        {
        $swf_return->result = 'FILE_NOT_WRITTEN';
        $swf_return->message = $dataroot_path.' user files directory is not writeable.';
        return $swf_return;
        }
        }

        // If previous file by user exists, delete it - Does not affect files with date + time in file name
        if(file_exists($dataroot_path.'f2.jpg'))
        {
        if(!unlink($dataroot_path.'f2.jpg'))
        {
        $swf_return->result = 'FILE_NOT_WRITTEN';
        $swf_return->message = $dataroot_path.' user files directory is not writeable.';
        return $swf_return;
        }
        }

        // Write the files to moodledata user directory
        $result = file_put_contents($dataroot_path.'f1.jpg', $obj['avatar']->data);
        $result = file_put_contents($dataroot_path.'f2.jpg', $obj['thumb']->data);

        // Tell Moodle that this user's avatar is active
        $data = new stdClass();
        $data->id = $USER->id;
        $data->picture = 1;
        if(!update_record('user',$data))
        {
        // This is unlikely but just in case there's some kind of sitewide setting...
        $swf_return->result = 'SUCCESS';
        $swf_return->message = 'Your avatar has been saved but your user account will not let you use it!';
        return $swf_return;
        }

        if($result)
        {
        $moodledata_path_filename_avatar_time = $user_path.'f1.jpg'.'?'.time();
        $moodledata_path_filename_thumb_time = $user_path.'f2.jpg'.'?'.time();
        $obj['feedback'] = '<a href="'.$moodledata_path_filename_avatar_time.'" target="_blank"><img src="'.$moodledata_path_filename_avatar_time.'" alt="saved image" width="100" height="100" border="0" /></a><a href="'.$moodledata_path_filename_thumb_time.'" target="_blank"><img src="'.$moodledata_path_filename_thumb_time.'" alt="saved image" width="35" height="35" border="0" /></a><br/>';
        // Push grade into Moodle grade book
        if($obj['feedbackformat'] < 10)
        {
        $obj['feedbackformat'] = 10; // Set a minimum 10 seconds so as not to trigger Wiki-like format errors.
        }
        if($obj['pushgrade']) {
        $swf_return->grade = $this->grades->amf_grade_update($obj);
        }
        $swf_return->result = 'SUCCESS';
        $swf_return->avatarurl = $moodledata_path_filename_avatar_time;
        $swf_return->thumburl = $moodledata_path_filename_thumb_time;
        $swf_return->message = 'Your avatar has been saved.';
        return $swf_return;
        } else {
        $swf_return->result = 'FILE_NOT_WRITTEN';
        $swf_return->avatarurl = $moodledata_path_filename_avatar_time;
        $swf_return->thumburl = $moodledata_path_filename_thumb_time;
        $swf_return->message = 'There was a problem. Your avatar has not been saved.';
        return $swf_return;
        }
        }
        $swf_return->result = 'NO_PERMISSION';
        $swf_return->avatarurl = 'You do not have permission to save avatars.'; // To be deprecated - Don't send messages on this parameter
        $swf_return->message = 'You do not have permission to save avatars.';
        return $swf_return;
        }

    // -----------------------------------------------------------------------------------------------------------

    /**
    * Clean up objects and variables for garbage collector
    *
    */
    public function __destruct()
    {
        unset($access);
        unset($users);
    }
    }
// No, I haven't forgotten the closing PHP tag. It's not necessary here.