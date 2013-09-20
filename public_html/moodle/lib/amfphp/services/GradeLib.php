<?php
/** This service passes grade data between Flash and Moodle gradelib API for SWF Activity Module
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

class GradeLib
{
	private $access;
	
	public function __construct()
	{
		global $CFG;
		require_once($CFG->libdir.'/gradelib.php'); // http://site.com/lib/gradelib.php (start of public API for grade book)
		
		// Check current user's capabilities
		require_once('Access.php');
		$this->access = new Access();
	}
	
	/**
	* Submit new or update grade; update/create grade_item definition. Grade must have userid specified, rawgrade and feedback with format are optional. rawgrade NULL means 'Not graded', missing property or key means do not change existing.
	*
	* Only following grade item properties can be changed 'itemname', 'idnumber', 'gradetype', 'grademax', 'grademin', 'scaleid', 'multfactor', 'plusfactor', 'deleted' and 'hidden'. 'reset' means delete all current grades including locked ones.
	*
	* Manual, course or category items can not be updated by this function.
	* 
	* @param obj
	* @return obj
	*/
	public function amf_grade_update($obj=NULL)
	{
		$obj['instance'] = 76;
		$obj['swfid'] = 4;
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades){
			//
			/*$source = 'mod/swf'; // $obj->['source']; // string source of the grade such as 'mod/assignment'
			$courseid = $capabilities->course; // $obj->['courseid']; // ID of course
			$itemtype = 'mod'; // $obj->['itemtype']; // type of grade item - mod, block
			$itemmodule = 'swf'; // $obj->['itemmodule']; // more specific than $itemtype - assignment, forum, etc.; maybe NULL for some item types
			$iteminstance = $obj['instance']; // $obj->['iteminstance']; // instance ID of graded subject
			$itemnumber = $obj['itemnumber']; // most probably 0, modules can use other numbers when having more than one grades for each user
			$grades = $obj['grades']; // grade (object, array) or several grades (arrays of arrays or objects), NULL if updating grade_item definition only
			$itemdetails = $obj['itemdetails']; // object or array describing the grading item, NULL if no change*/
			//
			$source = 'mod/quiz';
			$courseid = $capabilities->course;
			$itemtype = 'mod';
			$itemmodule = 'quiz';
			$iteminstance = $obj['instance'];
			$itemnumber = 0;
			$grades = array();
			$grades[0] = 69;
			//$itemdetails = $obj['itemdetails'];
			// Call lib/gradelib.php public API function
			$result = grade_update($source, $courseid, $itemtype, $itemmodule, $iteminstance, $itemnumber, $grades);//, $itemdetails);
			return $result;
		}
	}
	
	/**
	* Updates outcomes of user
	* Manual outcomes can not be updated.
	*
	* @param
	* @return
	*/
	public function amf_grade_update_outcomes($obj=NULL)
	{
		$obj['instance'] = 71;
		$obj['swfid'] = 4;
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades){
			global $USER;
			//
			$source = 'mod/swf'; // $obj['source']; // string source of the grade such as 'mod/assignment'
			$courseid = $capabilities->course; // $obj['courseid']; // ID of course
			$itemtype = 'mod'; // $obj['itemtype']; // type of grade item - mod, block
			$itemmodule = 'swf'; // $obj['itemmodule']; // more specific than $itemtype - assignment, forum, etc.; maybe NULL for some item types
			$iteminstance = $obj['instance']; // $obj['iteminstance']; // instance ID of graded subject
			$userid = $USER->id; // $obj['userid']; // ID of the graded user
			$data = $obj['data']; // array itemnumber=>outcomegrade
			// Call lib/gradelib.php public API function
			$result = grade_update_outcomes($source, $courseid, $itemtype, $itemmodule, $iteminstance, $userid, $data);
			return $result;
		}
	}
	
	/**
	* Returns grading information for given activity - optionally with users grades
	* Manual, course or category items can not be queried.
	* 
	* @param
	* @return
	*/
	public function amf_grade_get_grades($obj=NULL)
	{
		// SEt up dummy data for testing
		$obj['instance'] = 76;
		$obj['swfid'] = 4;
		$obj['itemtype'] = 'mod';
		$obj['itemmodule'] = 'quiz';
		$obj['userid_or_ids'] = array(1,2,3);
		//
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		if ($capabilities->is_logged_in && $capabilities->view_own_grades){
			//
			$courseid = $capabilities->course; // ID of course
			$itemtype = $obj['itemtype']; // type of grade item - mod, block
			$itemmodule = $obj['itemmodule']; // more specific than $itemtype - assignment, forum, etc.; maybe NULL for some item types
			$iteminstance = $obj['instance']; // $obj['iteminstance']; // instance ID of graded subject
			$userid_or_ids = $obj['userid_or_ids']; // $obj['userid_or_ids']; // optional id of the graded user or array of ids; if userid not used, returns only information about grade_item
			//
			// Call lib/gradelib.php public API function
			$result = grade_get_grades($courseid, $itemtype, $itemmodule, $iteminstance, $userid_or_ids);
			return $result;
		}
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