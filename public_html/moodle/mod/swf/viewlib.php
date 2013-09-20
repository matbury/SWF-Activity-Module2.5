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
 * @package     mod_swf
 * @copyright   2013 Matt Bury
 * @author      Matt Bury <matt@matbury.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once('../../lib/filelib.php'); // For HTML5 embed params

// Contains specific functions for moodle/mod/swf/view.php

/**
 * Build flashvars.name = "value"; pair embed code
 * @param object $swf
 * @return string
 */
function swf_get_namevaluepairs($swf) {
    // Don't print empty FlashVars name=value pairs
    $swf_namevaluepairs = '';
    if($swf->name1 != '' && $swf->value1 != '') {
        $swf_namevaluepairs .= '
                flashvars.'.$swf->name1.' = "'.urlencode($swf->value1).'";';
    }
    if($swf->name2 != '' && $swf->value2 != '') {
        $swf_namevaluepairs .= '
                flashvars.'.$swf->name2.' = "'.urlencode($swf->value2).'";';
    }
    if($swf->name3 != '' && $swf->value3 != '') {
        $swf_namevaluepairs .= '
                flashvars.'.$swf->name3.' = "'.urlencode($swf->value3).'";';
    }
    return $swf_namevaluepairs;
}

/**
 * Gets an absolute URL to a file uploaded into Moodle filemanager
 * @global type $CFG
 * @param type $swf
 * @param type $context
 * @return type string An absolute URL to the XML or SMIL file
 */
function swf_get_fileurl($swf, $context){
    global $CFG;
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_swf', 'content', 0, 'sortorder DESC, id ASC', false);
    if (count($files) < 1) {
        die;
    } else {
        $file = reset($files);
        unset($files);
    }
    $path = '/'.$context->id.'/mod_swf/content/0'.$file->get_filepath().$file->get_filename();
    $swf_fileurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
    return $swf_fileurl;
}

/**
 * Alternative code to display in case Flash embed fails
 * @param type $swf
 * @return type
 */
function swf_get_alternative_content($swf) {
    return '<div style="text-align: center; font-size: large; font-weight: bold;">
            <div>&nbsp;</div>
            <div>'.get_string('broken1','swf').'</div>
            <div>&nbsp;</div>
            <div>
                <div>'.get_string('broken2','swf').'</div>
                <div>'.get_string('broken3','swf').$swf->version.get_string('broken31','swf').'</div>
                <div>'.get_string('broken4','swf').'</div>
                <div>'.get_string('broken5','swf').'</div>
            </div>
            <div>&nbsp;</div>
            <div>'.get_string('broken6','swf').'</div>
            <div>'.get_string('broken7','swf').'</div>
            <div>&nbsp;</div>
            <div><a href="http://helpx.adobe.com/flash-player.html" target="_blank" alt="'.get_string('broken8','swf').'" title="'.get_string('broken8','swf').'"><img src="pix/fp_logo.png" alt="Flash Player logo"/></a></div>
            <div>&nbsp;</div>
            <div><a href="http://matbury.com/" target="_blank" title="Matt Bury | matbury.com">© 2013 Matt Bury | matbury.com</a></div>
            <div>&nbsp;</div>
            <div><a href="http://www.gnu.org/licenses/gpl.html" target="_blank" title="GNU GPL v3 licensed">GNU GPL v3 licensed</a></div></div>';
}

/**
 * Generates HTML5 video embed code in case Flash embed fails
 * @param string $swf_xmlurl
 * @return string
 */
function swf_get_html5_embed($swf_xmlurl) {
    $swf_path_info = pathinfo($swf_xmlurl);
    $swf_mime_type = mimeinfo('type', $swf_path_info['basename']);
    return '<div style="text-align: center;">
        <div>&nbsp;</div>
        <video controls>
            <source src="'.$swf_xmlurl.'" type="'.$swf_mime_type.'"/>
            '.get_string('nohtml5','swf').'
        </video><div><a href="'.$swf_xmlurl.'" title="'.get_string('downloadrightclick','swf').'">'.get_string('download','swf').'</div></div>';
}

/**
 * Generates plugin FlashVars
 * @param type $swf
 * @return string
 */
function swf_get_plugins($swf) {
    switch($swf->plugin) {
        case 'strobe_smil': // Plugin ID from DB
            $swf_plugin = 'flashvars.plugin_smil = "plugins/SMILPlugin.swf";'; // Actual URL
            break;
        case 'strobe_youtube':
            $swf_plugin = 'flashvars.plugin_YouTubePlugin = "plugins/YouTubePlugin.swf";';
            break;
        /*case 'strobe_captioning': // Not yet implemented
            $swf_plugin = 'flashvars.plugin_Captioning = "plugins/Captioning.swf";';
            break;*/
        default:
            $swf_plugin = '';
    }
    return $swf_plugin;
}

/**
 * Generates fullscreen mode Flash params
 * @param type $swf
 * @return string
 */
function swf_get_allowfullscreen($swf) {
    if($swf->allowfullscreen === 'false') {
        return 'params.allowFullScreen = "false";';
    }
    if($swf->allowfullscreen === 'allowFullScreen') {
        return 'params.allowFullScreen = "true";';
    }
    // so it must be allowFullScreenInteractive
    return 'params.allowFullScreenInteractive = "true";';
}

/**
 * Generates navigation bar at top of view page
 * @global type $CFG
 * @global type $COURSE
 * @param type $swf
 * @return type
 */
function swf_get_navbar($swf) {
    if($swf->shownavbar == 'true')
    {
    global $CFG, $COURSE, $USER;
    $swf_usermodified = '';
    
    if(has_capability('mod/swf:addinstance',$swf->context,$USER->id,false)) {
        $swf_usermodified = '('.get_string('usermodified','swf').' '.$USER->firstname.' '.$USER->lastname.' '.date('H:i Y-m-d',usertime($swf->timemodified)).')';
    }
    if($COURSE->showgrades == 1) {
        $swf_right_links = '<div style="float:right; padding-right: 3px;">
                <a href="index.php?id='.$COURSE->id.'" title="'.get_string('report','swf').'" >'.get_string('report','swf').'</a> | <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$COURSE->id.'" title="'.get_string('gradebook_title','swf').'" >'.get_string('gradebook','swf').'</a> | <a href="http://docs.moodle.org/25/en/SWF_Activity_Module" title="'.get_string('swfhelp_title','swf').'" target="_blank" >'.get_string('swfhelp','swf').'</a>
            </div>';
    } else {
        $swf_right_links = '<div style="float:right; padding-right: 3px;">
                <a href="index.php?id='.$COURSE->id.'" title="'.get_string('report','swf').'" >'.get_string('report','swf').'</a> | <a href="http://docs.moodle.org/25/en/SWF_Activity_Module" title="'.get_string('swfhelp_title','swf').'" target="_blank" >'.get_string('swfhelp','swf').'</a>
            </div>';
    }
    return '<div class="swfnavdiv">
                <a href="'.$CFG->wwwroot.'/" title="'.get_string('home').'" >'.get_string('home').'</a> >> <a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'" title="'.$COURSE->shortname.'" >'.$COURSE->shortname.'</a> >> <strong>'.$swf->name.'</strong> '.$swf_usermodified.'
                '.$swf_right_links.'
            </div>';
    }
    return ''; // no nav bar
}