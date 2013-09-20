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
        $capabilities = new object();
        
        $capabilities->is_logged_in = isloggedin();
        // Check user is logged in
        if(!$capabilities->is_logged_in)
        {
            $capabilities->result = 'NOT_LOGGED_IN';
            $capabilities->message = get_string('not_logged_in','swf');
            return $capabilities;
        }
        
        if ($a) {
            if (!$swf = $DB->get_record('swf', array('id'=>$a))) {
                $capabilities->result = 'INVALID_MODULE';
                $capabilities->message = get_string('invalid_module','swf');
                return $capabilities;
            }
            $cm = get_coursemodule_from_instance('swf', $swf->id, $swf->course, false, MUST_EXIST);
        } else {
            if (!$cm = get_coursemodule_from_id('swf', $id)) {
                $capabilities->result = 'INVALID_MODULE';
                $capabilities->message = get_string('invalid_module','swf');
                return $capabilities;
            }
            $swf = $DB->get_record('swf', array('id'=>$cm->instance), '*', MUST_EXIST);
        }

        $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        // Add course module details and capabilities
        $capabilities->course = $cm->course;
        $capabilities->swfname = $swf->name;
        $capabilities->courseobject = $course;
        $capabilities->cmid = $cm->id;
        $capabilities->context = $context;
        $capabilities->view_mod = has_capability('mod/swf:view', $context); // Is guest
        $capabilities->view_own_grades = has_capability('mod/swf:submit', $context); // Is student
        $capabilities->view_all_grades = has_capability('mod/swf:viewall', $context); // Is teacher

        return $capabilities;
    }

    public function __destruct()
    {
        // Do nothing here.
    }
}
