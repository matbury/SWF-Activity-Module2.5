<?php
/** This service handles user info for SWF Activity Module
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: view.php,v 0.1 2009/02/21 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* Copyright (C) 2009  Matt Bury
*
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Users
{
	private $access;
	private $groups;
	
	public function __construct()
	{
		// Authenticate and check current user's capabilities
		require_once('Access.php');
		$this->access = new Access();
		// Groups API
		require_once('Groups.php');
		$this->groups = new Groups();
	}
	
	// ------------------------------------------------------------ PUBLIC FUNCTIONS ------------------------------------------------------------ //
	/** Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
	* 
	* @return string
	*/
	public function amf_ping($obj = null)
	{
		$swf_return->result = 'SUCCESS';
		$swf_return->message = 'Users.amf_ping successfully called.';
		$swf_return->service_version = '2009.02.21';
		$swf_return->php_version = phpversion(); // get current server PHP version
		return $swf_return;
	}
	
	/** Get user by ID
	* 
	* @param obj
	* @return obj
	*/
	public function amf_get_user($obj)
	{
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		$obj['userid'] = 3;
		*/
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades)
		{
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_user id:'.$obj['userid'], "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$record = $DB->get_record('user','id',$obj['userid']);
			// Only send safe data, i.e. no passwords, user names, etc.
			if($record)
			{
				$swf_return->records = $this->convert_record_to_object($record);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Users successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	/** Get current user by ID
	* 
	* @param obj
	* @return obj
	*/
	public function amf_get_self($obj)
	{
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		*/
		global $USER;
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades){
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_self', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$record = $DB->get_record('user','id',$USER->id);
			// Only send safe data, i.e. no passwords, user names, etc.
			if($record) {
				$swf_return->records = $this->convert_record_to_object($record);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Users successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	/** Get all groups on current course
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_groups($obj)
	{
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		*/
		global $CFG;
		global $USER;
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp user_group', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = user_group($capabilities->course, $USER->id);
			$result = array();
			foreach($records as $record)
			{
				$group_item = new object();
				$group_item->courseid = $record->courseid;
				$group_item->description = $record->description;
				$group_item->id = $record->id;
				$group_item->name = $record->name;
				$group_item->timecreated = $record->timecreated;
				$group_item->timemodified = $record->timemodified;
				$group_item->picture = $record->picture;
				$group_item->hidepicture = $record->hidepicture;
				array_push($result,$record);
			}
			$swf_return->records = $result;
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Users successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	/** Get all groups on current course
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_all_groups($obj) {
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		*/
		global $CFG;
		global $USER;
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp groups_get_all_groups', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			//groups_get_all_groups($courseid, $userid=0, $groupingid=0, $fields='g.*')
			$swf_return->records = groups_get_all_groups($capabilities->course, $userid=0, $groupingid=0, $fields='g.*');
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Users successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	/** Get all users in group on current course - !!!not working yet!
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_group_users($obj)
	{
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		*/
		global $CFG;
		global $USER;
		
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades){
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_group_users', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = user_group($capabilities->course, $USER->id);
			if(!$records){
				$g_id = 0;
			} else {
				foreach($records as $record=>$xxx) {
					$g_id = $xxx->id;
				}
			}
			//
			return $records; // returns groups
			//
			if($records)
			{
				$swf_return->records = $this->convert_records_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Users successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}

	/** Get all users on current course
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_course_users($obj)
	{
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		*/
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_course_users', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = get_course_users($capabilities->course);
			if($records)
			{
				$swf_return->records = $this->convert_records_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Users successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	/** Get all teachers on current course
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_course_teachers($obj)
	{
		/*
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		*/
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades)
		{
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_course_teachers', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = get_course_teachers($capabilities->course);
			if($records)
			{
				$swf_return->records = $this->convert_records_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Users successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	// ------------------------------------------------------------ PRIVATE FUNCTIONS ------------------------------------------------------------ //
	/** Convert single DB user record to an object that's safe to send client-side
	* 
	* @param obj
	* @return array
	*/
	private function convert_record_to_object($record)
	{
		global $CFG;
		$result = new object();
		$result->id = $record->id;
		$result->firstname = $record->firstname;
		$result->lastname = $record->lastname;
		$result->fullname = $record->firstname.' '.$record->lastname; // more convenient way to access it
		$result->city = $record->city;
		$result->country = $record->country;
		$result->email = $record->email;
		$result->skype = $record->skype;
		$result->msn = $record->msn;
		$result->yahoo = $record->yahoo;
		$result->aim = $record->aim;
		$result->phone1 = $record->phone1;
		$result->phone2 = $record->phone2;
		$result->url = $records->url;
		$result->timezone = $record->timezone;
		$result->avatar = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f2.jpg'; // path to small user pic
		$result->picture = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f1.jpg'; // path to large user pic
		return $result;
	}
	
	/** Convert DB user records to array that's safe to send client-side
	* 
	* @param obj
	* @return array
	*/
	private function convert_records_to_array($records)
	{
		global $CFG;
		$result = array();
		foreach($records as $record)
		{
				$userData = new object();
				$userData->id = $record->id;
				$userData->firstname = $record->firstname;
				$userData->lastname = $record->lastname;
				$userData->fullname = $record->firstname.' '.$record->lastname; // more convenient way to access it
				$userData->city = $record->city;
				$userData->country = $record->country;
				$userData->email = $record->email;
				$userData->skype = $record->skype;
				$userData->msn = $record->msn;
				$userData->yahoo = $record->yahoo;
				$userData->aim = $record->aim;
				$userData->phone1 = $record->phone1;
				$userData->phone2 = $record->phone2;
				$userData->url = $records->url;
				$userData->timezone = $record->timezone;
				$userData->avatar = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f2.jpg'; // path to small user pic
				$userData->picture = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f1.jpg'; // path to large user pic
				array_push($result,$userData);
		}
		return $result;
	}
	// -----------------------------------------------------------------------------------------------------------
	
	/** Clean up objects and variables for garbage collector
	* 
	*/
	public function __destruct()
	{
		unset($access);
	}
}
// No, I haven't forgotten the closing PHP tag. It's not necessary here.