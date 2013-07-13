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
 * @package     mod
 * @package     swf
 * @copyright   2013 Matt Bury
 * @author      Matt Bury <matt@matbury.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** Include eventslib.php */
require_once($CFG->libdir.'/eventslib.php');
/** Include formslib.php */
require_once($CFG->libdir.'/formslib.php');
/** Include calendar/lib.php */
require_once($CFG->dirroot.'/calendar/lib.php');


////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 * 
 * @todo: review features before publishing the module
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function swf_supports($feature) {
    switch($feature) {
//        case FEATURE_GROUPS:                  return true;
//        case FEATURE_GROUPINGS:               return true;
//        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
//        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
//        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
//        case FEATURE_GRADE_OUTCOMES:          return true;
//        case FEATURE_RATE:                    return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
//        case FEATURE_SHOW_DESCRIPTION:        return true;
//        case FEATURE_ADVANCED_GRADING:        return true;
        default:                        
          if (defined('FEATURE_SHOW_DESCRIPTION') && $feature == FEATURE_SHOW_DESCRIPTION) return true;
          else return null;
    }
}

/**
 * Saves a new instance of the swf into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 * 
 * @todo: create event (when timedue added)
 *
 * @param object $swf An object from the form in mod_form.php
 * @param mod_swf_mod_form $mform
 * @return int The id of the newly inserted swf record
 */
function swf_add_instance(stdClass $swf, mod_swf_mod_form $mform = null) { // Throws error without a local XML/SMIL/SWF files uploaded
    global $DB, $USER;
    $cmid = $swf->coursemodule;
    $swf->timecreated = time();
    $swf->timemodified = $swf->timecreated;
    $swf->usermodified = $USER->id;
    if ($swf->grade >=0 ) {
        $swf->maxgrade = $swf->grade;
    }
    $swf->id = $DB->insert_record('swf', $swf);
    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $swf->id, array('id'=>$cmid));
    // Store the SWF and verify
    if ($mform->get_data()->xmlurl === 'true') {
        $filename = swf_set_mainfile($swf);
        $swf->fileurl = $filename;
        $DB->update_record('swf', $swf);
    }
    swf_grade_item_update($swf);
    return $swf->id;    
}

/**
 * Updates an instance of the swf in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $swf An object from the form in mod_form.php
 * @param mod_swf_mod_form $mform
 * @return boolean Success/Fail
 */
function swf_update_instance(stdClass $swf, mod_swf_mod_form $mform = null) {
    global $DB, $USER;
    $swf->timemodified = time();
    $swf->usermodified = $USER->id;
    $swf->id = $swf->instance;
    if ($swf->grade >=0 ) {
        $swf->maxgrade = $swf->grade;
    }
    $result = $DB->update_record('swf', $swf);
    if ($result && $mform->get_data()->xmlurl === 'true') {
        $filename = swf_set_mainfile($swf);
        $swf->fileurl = $filename;
        $result = $DB->update_record('swf', $swf);
    }
    if ($result){
        // get existing grade item
        $result = swf_grade_item_update($swf);
    }
    return $result;
}

/**
 * Removes an instance of the swf from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 * 
 * @todo: delete event records (after adding this feature to the module)
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function swf_delete_instance($id) {
    global $DB;
    if (!$swf = $DB->get_record('swf', array('id'=>$id))) {
        return false;
    }
    swf_grade_item_delete($swf); // Delete grade item
    $result = $DB->delete_records('swf', array('id' => $id)); // delete module instance
    return $result;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $swf
 * @return stdClass|null
 */
function swf_user_outline($course, $user, $mod, $swf) {    
    global $CFG;
    require_once("$CFG->libdir/gradelib.php");
    $result = null;
    $grades = grade_get_grades($course->id, 'mod', 'swf', $swf->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        $result = new stdClass();
        $result->info = get_string('grade') . ': ' . $grade->str_long_grade;
        //if grade was last modified by the user themselves use date graded. Otherwise use date submitted
        if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
            $result->time = $grade->dategraded;
        } else {
            $result->time = $grade->datesubmitted;
        }
    }
    return $result;    
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 * 
 * @todo: implement
 *
 * @return string HTML
 */
function swf_user_complete($course, $user, $mod, $swf) {
    $outline = swf_user_outline($course, $user, $mod, $swf);
    print_r($outline->info);
    return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in swf activities and print it out.
 * Return true if there was output, or false is there was none.
 * 
 * @todo: implement
 *
 * @return boolean
 */
function swf_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Returns all activity in swfs since a given time
 * 
 * @todo: implement
 *
 * @param array $activities sequentially indexed array of objects
 * @param int $index
 * @param int $timestart
 * @param int $courseid
 * @param int $cmid
 * @param int $userid defaults to 0
 * @param int $groupid defaults to 0
 * @return void adds items into $activities and increases $index
 */
function swf_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    return;
}

/**
 * Prints single activity item prepared by {@see swf_get_recent_mod_activity()}
 * 
 * @todo: implement
 * 
 * @return void
 */
function swf_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    echo '';
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function swf_cron () {
    return true;
}

/**
 * Returns an array of users who are participating in this swf
 *
 * Must return an array of users who are participants for a given instance
 * of swf. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 * 
 * @param int $swfid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function swf_get_participants($swfid) {
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function swf_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/grade:managegradingforms');
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of swf?
 *
 * This function returns if a scale is being used by one swf
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $swfid ID of an instance of this module
 * @return bool true if the scale is used by the given swf instance
 */
function swf_scale_used($swfid, $scaleid) {
    global $DB;
    $return = false;
    $rec = $DB->get_record('swf', array('id'=>$swfid,'grade'=>-$scaleid));
    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }
    return $return;
}

/**
 * Checks if scale is being used by any instance of swf.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any swf instance
 */
function swf_scale_used_anywhere($scaleid) {
    global $DB;
    if ($scaleid and $DB->record_exists('swf', array('grade'=>-$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give swf instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @uses GRADE_TYPE_NONE
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_SCALE
 * @param stdClass $swf instance object with extra cmidnumber and modname property
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok
 */
function swf_grade_item_update(stdClass $swf, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    if (!isset($swf->courseid)) {
        $swf->courseid = $swf->course;
    }
    $params = array();
    $params['itemname'] = clean_param($swf->name, PARAM_NOTAGS);
    $params['idnumber'] = $swf->cmidnumber;
    if ($swf->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $swf->grade;
        $params['grademin']  = 0;
    } /*else if ($swf->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$swf->grade;
    }*/ else {
        $params['gradetype'] = GRADE_TYPE_NONE; // allow text comments only
    }
    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }
    grade_update('mod/swf', $swf->courseid, 'mod', 'swf', $swf->id, 0, $grades, $params);
    return true;
}

/**
 * Delete grade item for given swf
 *
 * @global object $CFG
 * @param object $swf 
 * @return object grade_item
 */
function swf_grade_item_delete($swf) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    grade_update('mod/swf', $swf->course, 'mod', 'swf', $swf->id, 0, null, array('deleted'=>1));
    return true;
}

/**
 * Return grade for given user or all users.
 *
 * @todo: implement userid=0 (all users)
 * @todo: optimize this function (to avoid call swf_get_sessions_summary or update only mandatory info)
 * 
 * @param object $swf object
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function swf_get_user_grades($swf, $userid=0) {
    global $CFG, $DB;
    //require_once($CFG->dirroot.'/mod/swf/locallib.php');
    require_once('../../lib/gradelib.php'); 
    // sanity check on $swf->id
    if (! isset($swf->id)) {
        return;
    }
    $grades = grade_get_grades($course->id,'mod','swf',$swf_instance->id,$userid);
    /*$sessions_summary = swf_get_sessions_summary($swf->id, $userid);
    $grades[$userid]->userid = $userid;
    $grades[$userid]->attempts = $sessions_summary->attempts;
    $grades[$userid]->totaltime = $sessions_summary->totaltime;
    $grades[$userid]->starttime = $sessions_summary->starttime;
    $grades[$userid]->done = $sessions_summary->done;
    $grades[$userid]->rawgrade = 0;
    if ($swf->avaluation=='score'){
        $grades[$userid]->rawgrade = $sessions_summary->score;				
    }else{
        $grades[$userid]->rawgrade = $sessions_summary->solved;
    }*/
    return $grades;
}

/**
 * Update swf grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 * 
 * @todo: Fix some problems (this function is not working when is called from beans.php)
 *
 * @param stdClass $swf instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @param boolean $nullifnone return null if grade does not exist
 * @return void
 */
function swf_update_grades(stdClass $swf, $userid = 0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');
    if ($swf->grade == 0) {
        swf_grade_item_update($swf);

    } else if ($grades = swf_get_user_grades($swf, $userid)) {
        foreach($grades as $k=>$v) {
            if ($v->rawgrade == -1) {
                $grades[$k]->rawgrade = null;
            }
        }
        swf_grade_item_update($swf, $grades);
    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        swf_grade_item_update($swf, $grade);
    } else {
        swf_grade_item_update($swf);
    }
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function swf_get_file_areas($course, $cm, $context) {
    return array(
        'content' => get_string('urledit',  'swf')
    );
}

/**
 * File browsing support for swf module content area.
 * @param object $browser
 * @param object $areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return object file_info instance or null if not found
 */
function swf_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;
    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }
    $fs = get_file_storage();
    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_swf', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_swf', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, false, false);
    }
    // note: swf_intro handled in file_browser automatically
    return null;
}


/**
 * Serves the files from the swf file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function swf_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload) {
    global $DB, $CFG;
    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }
    require_login($course, true, $cm);
    if (!has_capability('mod/swf:view', $context)) {
        return false;
    }
    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }
    array_shift($args); // ignore revision - designed to prevent caching problems only
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = rtrim("/$context->id/mod_swf/$filearea/0/$relativepath", '/');
    do {
        if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
            break;
        }
    } while (false);
    // finally send the file
    send_stored_file($file, 86400, 0, $forcedownload);    
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding swf nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the swf module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function swf_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
    //
}

/**
 * Extends the settings navigation with the swf settings
 *
 * This function is called when the context for the page is a swf module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $swfnode {@link navigation_node}
 */
function swf_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $swfnode=null) {
    //
}

////////////////////////////////////////////////////////////////////////////////
// Reset                                                                      //
////////////////////////////////////////////////////////////////////////////////


/**
 * Removes all grades from gradebook
 * @param int $courseid
 * @param string optional type
 */
function swf_reset_gradebook($courseid) {
    global $CFG, $DB;
    $params = array('courseid'=>$courseid);
    $sql = "SELECT j.*, cm.idnumber as cmidnumber, j.course as courseid
              FROM {swf} j, {course_modules} cm, {modules} m
             WHERE m.name='swf' AND m.id=cm.module AND cm.instance=j.id AND j.course=:courseid ";
    if ($swfs = $DB->get_records_sql($sql, $params)) {
        foreach ($swfs as $swf) {
            swf_grade_item_update($swf, 'reset');
        }
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified swf
 * and clean up any related data.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function swf_reset_userdata($data) {
    global $CFG, $DB;
    $componentstr = get_string('modulenameplural', 'choice');
    $status = array();
    if (!empty($data->reset_swf_deleteallsessions)) {
        /*$params = array('courseid' => $data->courseid);
        $select = 'session_id IN'
            . " (SELECT s.session_id FROM {swf_sessions} s"
            . " INNER JOIN {swf} j ON s.swfid = j.id"
            . " WHERE j.course = :courseid)";
        $DB->delete_records_select('swf_activities', $select, $params);
        $select = 'swfid IN'
            . " (SELECT j.id FROM {swf} j"
            . " WHERE j.course = :courseid)";
        $DB->delete_records_select('swf_sessions', $select, $params);*/
        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            swf_reset_gradebook($data->courseid);
        }
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallsessions', 'swf'), 'error'=>false);
    }
   return $status;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the swf.
 * @param $mform form passed by reference
 */
function swf_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'swfheader', get_string('modulenameplural', 'swf'));
    $mform->addElement('checkbox', 'reset_swf_deleteallsessions', get_string('deleteallsessions', 'swf'));
}

/**
 * Course reset form defaults.
 */
function swf_reset_course_form_defaults($course) {
    return array('reset_swf_deleteallsessions' => 1);
}
