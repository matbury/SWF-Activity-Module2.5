<?php
/**
*    Copyright (C) 2013  Matt Bury - matt@matbury.com - http://matbury.com/
*
*    This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Checks current user's login status and capabilities
*
* @author Matt Bury - matt@matbury.com
* @version $Id: Access.php, v 1.0 2013/01/22 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* @package lib/amfphp/services
**/

class Access
{
	// Single function so no class variables
	
	public function __contruct()
	{
		// Do nothing here
	}
	
	/**
	* Check login and capabilities of current user as defined in swf/db/access.php
	* Either parameter can be used
	* @param int course module ID
	* @param int swf ID
	* @return obj
	*
	*/
        public function get_capabilities($id = null, $a = null)
        {
            global $DB;
            // Object to store results
            $obj = new object();

            // Check user is logged in
            if(isloggedin())
            {
                    $obj->is_logged_in = true;
            } else {
                    $obj->is_logged_in = false;
                    return $obj; // Not logged in so stop here.
            }
            
            if ($p) {
                if (!$swf = $DB->get_record('swf', array('id'=>$p))) {
                    print_error('invalidaccessparameter');
                }
                $cm = get_coursemodule_from_instance('swf', $swf->id, $swf->course, false, MUST_EXIST);

            } else {
                if (!$cm = get_coursemodule_from_id('swf', $id)) {
                    print_error('invalidcoursemodule');
                }
                $swf = $DB->get_record('swf', array('id'=>$cm->instance), '*', MUST_EXIST);
            }

            $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
            $context = context_module::instance($cm->id);
            
            // Store module details
            $obj->course = $cm->course;
            $obj->swfname = $swf->name;
            $obj->courseobject = $course;
            $obj->cmid = $cm->id;
            //return $obj;
            
            // Check if current user can view module (is guest)
            if (has_capability('mod/swf:view', $context))
            {
                    $obj->view_mod = true;
            } else {
                    $obj->view_mod = false;
            }
            // Check if current user can view own grades (is user)
            if (has_capability('mod/swf:view', $context))
            {
                    $obj->view_own_grades = true;
            } else {
                    $obj->view_own_grades = false;
            }
            // Check if current user can view all grades (is teacher)
            if (has_capability('mod/swf:grade', $context))
            {
                    $obj->view_all_grades = true;
            } else {
                    $obj->view_all_grades = false;
            }
            // Add on context object
            $obj->context = $context;
            
            return $obj;
        }
	
	public function __destruct()
	{
		// Do nothing here.
	}
}
