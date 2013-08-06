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

require_once('../../config.php');
//require_once('locallib.php');
require_once('viewlib.php');
//require_once($CFG->libdir.'/completionlib.php');
//require_once('../../lib/completionlib.php');
//require_once('../../lib/filelib.php'); // For HTML5 embed params


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

// Print the page header
/*$PAGE->set_url('/mod/swf/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($swf->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Mark viewed if required
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

//has_capability('mod/swf:grade', $context, $USER->id, false)*/

/*
 * Please note, this page does not use Moodle's $PAGE renderer.
 * $PAGE prevents useful and desirable JS libraries such as SWFObject and
 * SWFAddress from working correctly.
 */

global $CFG, $USER, $COURSE;

$swf_namevaluepairs = swf_get_namevaluepairs($swf);

// Build xmlurl
if($swf->xmlurl === 'false')
{// nothing to load
    $swf_alternative_content = swf_get_alternative_content($swf);
    $swf_xmlurl = '';
} else if($swf->xmlurl === 'true') {// Build fileurl through Moodle file API
    $swf_xmlurl = swf_get_fileurl($swf, $context);
    // Which FlashVar does media player use?
    switch($swf->swfurl) {
        case 'swfs/StrobeMediaPlayback.swf': // Strobe = src
            $swf_alternative_content = swf_get_html5_embed($swf_xmlurl);
            $swf_xmlurl = 'flashvars.src = "'.$swf_xmlurl.'";';
            break;
        case 'swfs/player.swf': // JW Player = file
            $swf_alternative_content = swf_get_html5_embed($swf_xmlurl);
            $swf_xmlurl = 'flashvars.file = "'.$swf_xmlurl.'";';
            break;
        default: // default = xmlurl
            $swf_alternative_content = swf_get_alternative_content($swf);
            $swf_xmlurl = 'flashvars.'.$swf->xmlurlname.' = "'.$swf_xmlurl.'";';
    }
} else {
    // Not null and not file API so build link to repository proxy script
    $swf_alternative_content = swf_get_alternative_content($swf);
    $swf_xmlurl = 'flashvars.'.$swf->xmlurlname.' = "'.$CFG->wwwroot.'/mod/swf/content.php'.$swf->xmlurl.'?nocache='.time().'";'; // Don't cache
}

//Plugins
$swf_plugin = swf_get_plugins($swf);

// Allow full screen mode
$swf_allowfullscreen = swf_get_allowfullscreen($swf);

// Show Moodle grade book link on top nav bar?
$swf_navbar = swf_get_navbar($swf);

// Print HTML page
echo '<!DOCTYPE html>
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
                flashvars.configxml = "'.$swf->configxml.'";
                flashvars.'.$swf->exiturlname.' = "'.$swf->exiturl.'";
                flashvars.gateway = "'.$CFG->wwwroot.'/lib/amfphp/gateway.php";
                flashvars.gradebook = "'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$COURSE->id.'";
                flashvars.instance = "'.$id.'";
                flashvars.moodledata = "'.$CFG->wwwroot.'/mod/swf/content.php/";
                flashvars.name = "'.urlencode($swf->name).'";
                '.$swf_plugin.'
                flashvars.sessiontimeout = "'.$CFG->sessiontimeout.'";
                flashvars.swfid = "'.$swf->id.'";
                flashvars.swfversion = "'.$swf->version.'";
                flashvars.servertime = "'.time().'";
                flashvars.userid = "'.$USER->id.'";
                flashvars.wwwroot = "'.$CFG->wwwroot.'/";
                '.$swf_xmlurl.'
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
                swfobject.embedSWF("'.$swf->swfurl.'", "myAlternativeContent", "'.$swf->width.'", "'.$swf->height.'", "'.$swf->version.'", "js/expressInstall.swf", flashvars, params, attributes);
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
        </style>
    </head>
    <body>
        '.$swf_navbar.'
        <div id="myAlternativeContent">
            '.$swf_alternative_content.'
        </div>
    </body>
</html>';
