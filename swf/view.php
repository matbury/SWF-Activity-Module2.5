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

require_once('../../config.php');
require_once('viewlib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // swf instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('swf', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $swf  = $DB->get_record('swf', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $swf  = $DB->get_record('swf', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $swf->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('swf', $swf->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/swf:view', $context);

add_to_log($course->id, 'swf', 'view', "view.php?id={$cm->id}", $swf->name, $cm->id);

// Mark instance completed on view
//$completion = new completion_info($course);
//$completion->set_module_viewed($cm);

//$swf->instance = $id;
$swf->context = $context;

global $CFG, $USER, $COURSE;

$swf_namevaluepairs = swf_get_namevaluepairs($swf);

swf_check_customised_flashvars_names($swf);

// What's the content FlashVars name and where's the FlashVars source (value)?
switch($swf->xmlurltype) {
    case 'false':
        // Nothing
        $swf_xmlurl = '';
        break;
    /*case 'fmanager':
        // Use Moodle File manager file
        $swf_xmlurl = swf_get_fileurl($swf, $context);
        break;*/
    case 'fullurl':
        // Use URL as is
        $swf_xmlurl = $swf->xmlurl;
        break;
    case 'content':
        // Use content.php proxy
        $swf_xmlurl = $CFG->swf_data_url.$swf->xmlurl;
        break;
    default:
        // Use content.php proxy
        $swf_xmlurl = $CFG->swf_data_url.$swf->xmlurl;
}

$swf_contenturl = $swf->videourl; // Use this if content file is false
if($swf->contentfile != 'false') {
    $swf_contenturl = $CFG->swf_data_url.$swf->contentfile; // files in /moodledata/repository/swf/content/video/*/*.*
}
// Check for default apps in /moodle/mod/apps/
$swf_videourlname = 'src'; // default
switch($swf->swfurl) {
    case 'apps/StrobeMediaPlayback.swf': // Strobe Media Playback source name = src
        $swf_alternative_content = swf_get_html5_embed($swf_contenturl);
        $swf_url = $swf->swfurl;
        break;
    case 'apps/player.swf': // JW Player source name = file
        $swf_videourlname = 'file';
        $swf_alternative_content = swf_get_html5_embed($swf_contenturl);
        $swf_url = $swf->swfurl;
        break;
    case 'apps/preloader.swf': // Preloader source name = xmlurl
        $swf_alternative_content = swf_get_alternative_content($swf);
        $swf_url = $swf->swfurl;
        break;
    case 'apps/swf_activity_module_debugger.swf': // SWF Debugger source name = xmlurl
        $swf_alternative_content = swf_get_alternative_content($swf);
        $swf_url = $swf->swfurl;
        break;
    default:
        $swf_alternative_content = swf_get_alternative_content($swf);
        $swf_url = $CFG->swf_data_url.$swf->swfurl;
}

// Plugins
$swf_plugin = swf_get_plugins($swf);

// Allow/Disallow full screen mode
$swf_allowfullscreen = swf_get_allowfullscreen($swf);

// Show custom SWF navigation bar at top of screen
$swf_navbar = swf_get_navbar($swf);
$swf_fullbrowser = 'false';
if($swf->shownavbar == 'false')
{
    $swf_fullbrowser = 'true';
}

// For PHP 5.3: flashvars.gateway = "'.$CFG->wwwroot.'/lib/amfphp/gateway.php"; // Uses AmfPHP 1.9
// For PHP 5.3 and 5.4+: flashvars.gateway = "'.$CFG->wwwroot.'/lib/Amfphp/index.php"; // Uses AmfPHP 2.1 (Not yet working!)

// Print HTML page
$swf_html = '<!DOCTYPE html>
<html>
    <head>
        <title>'.$swf->name.'</title>
        <link rel="shortcut icon" href="'.$CFG->wwwroot.'/mod/swf/pix/icon.gif" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="pragma" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <script type="text/javascript" src="js/swfobject.js"></script>
        <script type="text/javascript">
                var flashvars = {};
                flashvars.'.$swf->apikeyname.' = "'.$swf->apikey.'";
                flashvars.'.$swf->configxmlname.' = "'.$swf->configxml.'";
                flashvars.courseid = "'.$COURSE->id.'";
                flashvars.coursepage = "'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'";
                flashvars.'.$swf->exiturlname.' = "'.$swf->exiturl.'";
                flashvars.fullbrowser = "'.$swf_fullbrowser.'";
                flashvars.fullscreen = "'.$swf->allowfullscreen.'";
                flashvars.gateway = "'.$CFG->wwwroot.'/lib/amfphp/gateway.php";
                flashvars.gradeupdate = "'.$CFG->wwwroot.'/mod/swf/scripts/gradeupdate.php";
                flashvars.gradebook = "'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$COURSE->id.'";
                flashvars.instance = "'.$id.'";
                flashvars.moodledata = "'.$CFG->swf_data_url.'";
                flashvars.name = "'.urlencode($swf->name).'";
                '.$swf_plugin.'
                flashvars.sessiontimeout = "'.$CFG->sessiontimeout.'";
                flashvars.'.$swf_videourlname.' = "'.$swf_contenturl.'";
                flashvars.swfid = "'.$swf->id.'";
                flashvars.swfversion = "'.$swf->version.'";
                flashvars.servertime = "'.time().'";
                flashvars.userid = "'.$USER->id.'";
                flashvars.wwwroot = "'.$CFG->wwwroot.'/";
                flashvars.'.$swf->xmlurlname.' = "'.$swf_xmlurl.'?nocache='.time().'";
                '.$swf_namevaluepairs.'
                var params = {};
                '.$swf_allowfullscreen.'
                params.allownetworking = "'.$swf->allownetworking.'";
                params.allowscriptaccess = "'.$swf->allowscriptaccess.'";
                params.allowseamlesstabbing = "true";
                params.bgcolor = "'.$swf->bgcolor.'";
                params.salign = "'.$swf->salign.'";
                params.scale = "'.$swf->scale.'";
                params.wmode = "window";
                var attributes = {};
                attributes.id = "mySwf";
                swfobject.embedSWF("'.$swf_url.'", "myAlternativeContent", "'.$swf->width.'", "'.$swf->height.'", "'.$swf->version.'", "js/expressInstall.swf", flashvars, params, attributes);
        </script>
        <style type="text/css">
            /* hide from ie on mac \*/
            html {
                    height: 100%;
                    overflow: hidden;
            }

            #flashcontent {
                    height: 100%;
            }
            /* end hide */
            body {
                    height: 100%;
                    margin: 0;
                    padding: 0;
                    background-color: #'.$swf->pagecolor.';
                    font-family: Arial;
                    text-align: center;
            }
            a:link {text-decoration: none; color: #dddddd;}
            a:visited {text-decoration: none; color: #dddddd;}
            a:hover {text-decoration: none; color: #ffffff; font-weight: bold;}
            a:active {text-decoration: none; color: #dddddd;}
            .swfnavdiv {
                color: #dddddd;
                background-color: #555555;
                width: 100%;
                padding-top: 1px;
                padding-bottom: 1px;
                padding-left: 3px;
                padding-right: 0px;
                text-align: left;
                float: left;
            }
            .swfalternativecontent {
                text-align: center;
                font-size: large;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        '.$swf_navbar.'
        <div id="myAlternativeContent">
            '.$swf_alternative_content.'
        </div>
    </body>
</html>';

echo $swf_html;
