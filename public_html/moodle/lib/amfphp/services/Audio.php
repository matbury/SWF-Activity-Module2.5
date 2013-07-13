<?php
/** This service handles messages for SWF Activity Module
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: Audio.php,v 1.0 2011/12/23 matbury Exp $
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

class Audio
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
	* @return boolean
	*/
	public function amf_ping($obj = null)
	{
		return true;
	}
	
	/**
	* Receives and saves an audio file sent from Flash app as a ByteArray
	*
	* @param instance (int) - Moodle module instance
	* @param swfid (int) - SWF Activity Module instance
	* @param bytearray (ByteArray) - audio file
	* 
	* @return (string) - either URL to saved image or error message
	*/
	public function amf_save_audio($obj)
	{
		/* 
		// To authenticate the AMFPHP browser app in Moodle, take at least one of
		// the following values from an existing instance of the SWF Activity Module
		$obj['instance'] = 2;
		$obj['swfid'] = 1;
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
			add_to_log($capabilities->course, 'swf', 'amfphp amf_save_snapshot', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			// strip out special characters
			$needles = array(' ','!','"','£','$','%','^','&','*','(',')','+','{','}',':',';','@','~','#','?','/','<','>','.','|','=','\'');
			$swf_dirname = str_replace($needles,'_',$capabilities->swfname); // directory name e.g. Concept_map_activity
			// Uncomment the second line if you want to keep previous versions of files
			$swf_filename = str_replace($needles,'_',$USER->lastname.'_'.$USER->firstname).'.'.$obj['audiotype']; // file name e.g. John_Smith.jpg
			//$swf_filename = str_replace($needles,'_',$USER->lastname.'_'.$USER->firstname).'_'.date('Y\_M\_dS\_h\-i\-s').'.'.$obj['imagetype']; // doesn't overwrite previously saved audio files
			
			// Build path to course files directory and subdirectory /recordings/17_Mic_recorder/
			$dataroot_path = $CFG->dataroot.'/'.$capabilities->course.'/recordings/'.$capabilities->cmid.'_'.$swf_dirname.'/';
			$moodledata_path = $CFG->wwwroot.'/file.php/'.$capabilities->course.'/recordings/'.$capabilities->cmid.'_'.$swf_dirname.'/';
			
			// If necessary, create directory with SWF Activity Module instance name,
			// e.g. /home/sites/example.com/moodledata/2/recordings/17_Mic_recorder/
			try {
				if(!file_exists($dataroot_path))
				{
					mkdir($dataroot_path, 0777, true);
				}
			} catch(Exception $e)
			{
				$swf_return->result = 'FILE_NOT_WRITTEN';
				$swf_return->audiourl = $dataroot_path;
				$swf_return->message = ''.$e->getMessage();
				return $swf_return;
			}
			
			// Can we write to this directory?
			if(!is_writeable($dataroot_path))
			{
				$swf_return->result = 'FILE_NOT_WRITTEN';
				$swf_return->audiourl = $dataroot_path;
				$swf_return->message = $dataroot_path.' course files directory is not writeable.';
				return $swf_return;
			}
			
			// If previous file by user exists, delete it - Does not affect files with date + time in file name
			if(file_exists($dataroot_path.$swf_filename))
			{
				if(!unlink($dataroot_path.$swf_filename))
				{
					$swf_return->result = 'FILE_NOT_WRITTEN';
					$swf_return->audiourl = $dataroot_path;
					$swf_return->message = $dataroot_path.' course files directory is not writeable.';
					return $swf_return;
				}
			}
			
			// Save it
			$result = file_put_contents($dataroot_path.$swf_filename, $obj['bytearray']->data);
			if($result)
			{
				$moodledata_path_filename_time = $moodledata_path.$swf_filename.'?'.time();
				$obj['feedback'] = $obj['feedback'].'<p><a href="'.$moodledata_path_filename_time.'" target="_blank">'.$swf_filename.' ('.$obj['feedbackformat'].' seconds)</a></p>';
				if($obj['feedbackformat'] < 10)
				{
					$obj['feedbackformat'] = 10; // Set a minimum 10 seconds so as not to trigger Wiki-like format errors.
				}
				// Push grade into Moodle grade book
				if($obj['pushgrade']) {
					$swf_return->grade = $this->grades->amf_grade_update($obj);
				}
				$swf_return->result = 'SUCCESS';
				$swf_return->imageurl = $moodledata_path_filename_time;
				$swf_return->message = 'Your audio file was successfully saved.';
				return $swf_return;
			} else {
				$swf_return->result = 'FILE_NOT_WRITTEN';
				$swf_return->imageurl = $moodledata_path_filename_time;
				$swf_return->message = 'There was a problem. Your audio file was not saved.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->imageurl = 'You do not have permission to save audio files.'; // To be deprecated - Don't send messages on this parameter
		$swf_return->message = 'You do not have permission to save audio files.';
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