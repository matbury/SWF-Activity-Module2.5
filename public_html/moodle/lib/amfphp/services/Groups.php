<?php
/** This service calls functions of lib/gradelib.php API
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

class Groups
{
	private $access;
	private $obj;
	
	public function __construct()
	{
		// Authenticate and check current user's capabilities
		require_once('Access.php');
		$this->access = new Access();
		// For testing in AMFPHP browser
		/*
		$this->obj['instance'] = 71;
		$this->obj['swfid'] = 4;
		$this->obj['groupid'] = 2;
		$this->obj['groupingid'] = 0;
		$this->obj['groupname'] = 'Matt\'s Group';
		$this->obj['groupingname'] = 'Matt\'s Grouping';
		$this->obj['userid'] = 2;
		*/
	}
	
	
	// ------------------------------------------------------------ PUBLIC FUNCTIONS ------------------------------------------------------------ //
	/** Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
	* 
	* @return string
	*/
	public function amf_ping($obj = null)
	{
		$swf_return->result = 'SUCCESS';
		$swf_return->message = 'Groups.amf_ping successfully called.';
		$swf_return->service_version = '2009.02.21';
		$swf_return->php_version = phpversion(); // get current server PHP version
		return $swf_return;
	}
	
	/** Check if a group exists
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_group_exists($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_group_exists', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$swf_return->records = groups_group_exists($obj['groupid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Group ID = '.$obj['groupid'].' exists.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get name of a group
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_group_name($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_group_name', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$swf_return->records = groups_get_group_name($obj['groupid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Group ID = '.$obj['groupid'].' name successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get name of a grouping
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_grouping_name($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_grouping_name', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$swf_return->records = groups_get_grouping_name($obj['groupid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Group ID = '.$obj['groupid'].' name successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get a group by name
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_group_by_name($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_group_by_name', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$swf_return->records = groups_get_group_by_name($obj['groupid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Group ID = '.$obj['groupid'].' successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get a grouping by name
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_grouping_by_name($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_grouping_by_name', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$swf_return->records = groups_get_grouping_by_name($obj['groupid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Group ID = '.$obj['groupid'].' successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get a group
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_group($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_group', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$record = groups_get_group($obj['groupid']);
			if($record)
			{
				$swf_return->records = $this->convert_record_to_array($record);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Group ID = '.$obj['groupid'].' successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get a grouping
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_grouping($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_grouping', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$record = groups_get_grouping($obj['groupingid']);
			if($record)
			{
				$swf_return->records = $this->convert_record_to_array($record);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Grouping ID = '.$obj['groupingid'].' successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get all groups on current course
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_all_groups($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_all_groups', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = groups_get_all_groups($capabilities->course,/*$obj['userid'],*/$obj['groupingid']);
			if($records)
			{
				$swf_return->records = $this->convert_record_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Grouping ID = '.$obj['groupingid'].' successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get user groups
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_user_groups($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_user_groups', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = groups_get_user_groups($obj['groupingid'],$obj['userid']);
			if($records)
			{
				$swf_return->records = $this->convert_record_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Grouping ID = '.$obj['groupingid'].' successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get all groupings
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_get_all_groupings($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_all_groupings', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = groups_get_all_groupings($capabilities->course);
			if($records)
			{
				$swf_return->records = $this->convert_record_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Course ID = '.$capabilities->course.' groupings successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Check if user is a member of a group
	* 
	* @param instance
	* @param swfid
	* @return object
	*/
	public function amf_is_member($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_is_member', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$swf_return->records = groups_is_member($obj['groupid'],$obj['userid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'User ID = '.$obj['userid'].' is member of Group ID = '.$obj['groupid'].'.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Check if user is member of any group
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_has_membership($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_has_membership', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$cm = new object();
			$cm->groupingid = $obj['groupingid'];
			$cm->course = $capabilities->course;
			$swf_return->records = groups_has_membership($cm, $obj['userid']);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'User ID = '.$obj['userid'].' is member of Grouping ID = '.$obj['groupingid'].'.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get members of a group
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_members($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_members', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = groups_get_members($obj['groupid']);
			if($records)
			{
				$swf_return->records = $this->convert_user_records_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get members of a grouping
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_grouping_members($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_grouping_members', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = groups_get_grouping_members($obj['groupingid']);
			if($records)
			{
				$swf_return->records = $this->convert_user_records_to_array($records);
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get course group mode
	* 
	* @param instance
	* @param swfid
	* @return int
	*/
	public function amf_get_course_groupmode($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			global $COURSE;
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_course_groupmode', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			//return groups_get_course_groupmode($COURSE);
			$swf_return->records = $COURSE->groupmode;
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get activity group mode
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_activity_groupmode($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			global $COURSE;
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_activity_groupmode', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$cm = new object();
			$cm->course = $capabilities->course;
			$swf_return->records = groups_get_activity_groupmode($cm,$COURSE);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Not tested yet!
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_print_course_menu($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			global $CFG;
			global $COURSE;
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_print_course_menu', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$urlroot = $CFG->wwwroot; // not sure what this should be
			$swf_return->records = groups_print_course_menu($COURSE, $urlroot);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Not tested yet!
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_print_activity_menu($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			global $CFG;
			global $COURSE;
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_print_activity_menu', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$cm = new object();
			$cm->groupingid = $obj['groupingid'];
			$cm->course = $capabilities->course;
			$cm->id = $obj['instance'];
			$urlroot = $CFG->wwwroot; // not sure what this should be
			$swf_return->records = groups_print_activity_menu($cm, $urlroot);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Get group for an activity module instance
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_activity_group($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_activity_group', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$cm = new object();
			$cm->groupingid = $obj['groupingid'];
			$cm->course = $capabilities->course;
			$cm->id = $obj['instance'];
			$swf_return->records = groups_get_activity_group($cm);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** 
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_get_activity_allowed_groups($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_get_activity_allowed_groups', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$cm = new object();
			$cm->groupingid = $obj['groupingid'];
			$cm->course = $capabilities->course;
			$cm->id = $obj['instance'];
			$swf_return->records = groups_get_activity_allowed_groups($cm);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	/** Not fully tested yet but seems to work
	* 
	* @param instance
	* @param swfid
	* @return array
	*/
	public function amf_course_module_visible($obj) {
		//
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_all_grades){
			//
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_course_module_visible', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$cm = new object();
			$cm->groupingid = $obj['groupingid'];
			$cm->course = $capabilities->course;
			$cm->id = $obj['instance'];
			//$cm->groupmembersonly = ??;
			$swf_return->records = groups_course_module_visible($cm);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Successfully accessed.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access groups.';
		return $swf_return;
	}
	
	// ------------------------------------------------------------ PRIVATE FUNCTIONS ------------------------------------------------------------ //
	/** Convert DB user record to object that's safe to send client-side
	* 
	* @param obj
	* @return array
	*/
	private function convert_record_to_array($record)
	{
		$result = new object();
		$result->courseid = $record->courseid;
		$result->description = $record->description;
		$result->id = $record->id;
		$result->name = $record->name;
		$result->timecreated = $record->timecreated;
		$result->timemodified = $record->timemodified;
		$result->picture = $record->picture;
		$result->hidepicture = $record->hidepicture;
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
		return $result;
	}
	
	/** Convert DB user records to array that's safe to send client-side
	* 
	* @param obj
	* @return array
	*/
	private function convert_user_records_to_array($records)
	{
		global $CFG;
		$result = array();
		foreach($records as $record)
		{
				$userData = new object();
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