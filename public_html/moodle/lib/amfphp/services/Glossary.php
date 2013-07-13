<?php
/** This service retrieves data from the Glossary activity module tables
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: Glossary.php,v 0.1 2011/07/18 matbury Exp $
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

All methods in this service class are course-wide only. 

Method List

public methods:
	amf_ping() // returns string 'Ping!' to check if connection was successful
	
	amf_grade_update() // creates or updates grade in grade_grades DB table
*/

class Glossary
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
	
	// ------------------------------------------------------------ PUBLIC FUNCTION ------------------------------------------------------------ //
	/** Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
	* 
	* @return string
	*/
	public function amf_ping($obj = null)
	{
		$swf_return->result = 'SUCCESS';
		$swf_return->message = 'Glossary.amf_ping successfully called.';
		$swf_return->service_version = '2011.07.18';
		$swf_return->php_version = phpversion(); // get current server PHP version
		return $swf_return;
	}
	
	
	/**
	* 
	* @param obj->instance int course instance ID
	* @param obj->swfid int swf instance ID
	* @param obj->feedback int/text feedback for user either elapsed time in seconds or text
	* @param obj->feedbackformat int format of feedback
	* @param obj->rawgrade int grade before calculation as a percentage, scale, etc.
	* @return array of grade information objects (scaleid, name, grade and locked status, etc.) indexed with itemnumbers
	*/
	public function amf_get_entries($obj)
	{
		// Dummy values for testing in service browser
		// Get the instance and swfid values from any instance of a SWF Activity Module and these values will work
		/* 
		$obj['instance'] = 249; // Course module ID, i.e. mod/swf/view.php?id=3
		$obj['swfid'] = 58; // SWF Activity Module ID
		$obj['glossaryid'] = 4; // Glossary activity module instance ID
		$obj['instanceid'] = 183; // Glossary activity module instance ID
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
			
			require_once($CFG->libdir.'/filelib.php');
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp glossary_entries id:'.$obj['glossaryid'], "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			// 
			if(!$swf_records = $DB->get_records('glossary_entries','glossaryid',$obj['glossaryid']))
			{
				// Send error string back to Flash client
				$swf_return->result = 'NO_GLOSSARY';
				$swf_return->message = 'Glossary ID = '.$obj['glossaryid'].' does not exist.';
				return $swf_return;
			}
			else
			{
				$swf_return->records = $this->amf_process_records($swf_records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Glossary ID = '.$obj['glossaryid'].' data successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to get glossary entries.';
		return $swf_return;
	}
	
	/**
	* Returns grade for the specified grade_item and user
	* @param obj->instance int course instance ID
	* @param obj->swfid int swf module ID
	* @param obj->userid int (optional) specify ID of user if teacher or admin
	* @return array of grade information objects (scaleid, name, grade and locked status, etc.) indexed with itemnumbers
	*/
	public function amf_get_glossaries($obj)
	{
		// Dummy values for testing in service browser
		// Get the instance and swfid values from any instance of a SWF Activity Module and these values will work
		/*
		$obj['instance'] = 174; // Course module ID, i.e. mod/swf/view.php?id=3
		$obj['swfid'] = 25; // SWF Activity Module ID
		$obj['glossaryid'] = 2; // Glossary activity module instance ID
		$obj['instanceid'] = 183; // Glossary activity module instance ID
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
			
			require_once($CFG->libdir.'/filelib.php');
			
			// 
			if(!$records = $DB->get_records('glossary','course',$capabilities->course))
			{
				// Send result back to Flash client
				$swf_return->result = 'NO_GLOSSARY';
				$swf_return->message = 'No glossaries found.';
				return $swf_return;
			}
			else
			{
				// Send result back to Flash client
				$swf_return->records = $this->amf_process_records($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Course ID = '.$capabilities->course.' Glossaries successfully accessed.';
				return $swf_return;	
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to get glossaries.';
		return $swf_return;
	}
	
	private function amf_process_records($records)
	{
		$result = array();
		foreach($records as $record)
		{
			array_push($result,$record);
		}
		return $result;
	}
	
	// -----------------------------------------------------------------------------------------------------------
	
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