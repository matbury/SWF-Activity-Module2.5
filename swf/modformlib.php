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
//require_once('../../lib/filelib.php'); // For HTML5 embed params

// Contains specific functions for moodle/mod/swf/mod_form.php

/**
 * List .swf files apps directory
 * @global object $CFG
 * @return array
 */
function swf_get_swfs() {
    global $CFG;
    $swf_urls = array('' => get_string('selectfile', 'swf'),
                    'apps/StrobeMediaPlayback.swf' => 'Strobe Media Player',
                    'apps/player.swf' => 'JW Player',
                    'apps/preloader.swf' => 'SWF Preloader',
                    'apps/swf_activity_module_debugger.swf' => 'SWF Debugger');
    foreach (glob($CFG->swf_data_dir.'apps/*.swf') as $swf_filename) {
        $swf_path_parts = pathinfo($swf_filename);
        $swf_filename = 'apps/'.$swf_path_parts['basename'];
        $swf_urls[$swf_filename] = 'apps/'.$swf_path_parts['basename'];
    }
    return $swf_urls;
}

function swf_get_plugins() {
    return array(''=>get_string('selectfile', 'swf'),
                'strobe_smil'=>'Strobe SMIL',
                'strobe_youtube'=>'Strobe YouTube');
                //'strobe_captioning'=>'Strobe Captioning'); // Not yet implemented
}

/**
* Get an array with Flash Params stage scale mode options
*
* @return array
*/
function swf_get_scale(){
  return array('noScale' => 'noScale',
      'showAll' => 'showAll',
      'exactFit' => 'exactFit',
      'noBorder' => 'noBorder');
} 

/**
* Get an array with Flash Params stage align options
*
* @return array
*/
function swf_get_salign(){
  return array('T' => 'top-center',
      'B' => 'bottom-center',
      'L' => 'center-left',
      'R' => 'center-right',
      'TL' => 'top-left',
      'TR' => 'top-right',
      'BL' => 'bottom-left',
      'BR' => 'bottom-right');
}

/**
* Get an array with general true/false options
*
* @return array
*/
function swf_get_truefalse(){
  return array('true' => 'true',
      'false' => 'false');
} 

/**
* Get an array with Flash Params allowscriptaccess options
*
* @return array
*/
function swf_get_allowscriptaccess(){
  return array('always' => 'always',
      'sameDomain' => 'sameDomain',
      'never' => 'never');
} 

/**
* Get an array with Flash Params allownetworking options
*
* @return array   
*/
function swf_get_allownetworking(){
  return array('all' => 'all',
      'internal' => 'internal',
      'none' => 'none');
} 

/**
* Get an array with Flash Params allowfullscreen options
*
* @return array   
*/
function swf_get_fullscreen_options(){
  return array('false' => 'false',
      'allowFullScreen' => 'allowFullScreen',
      'allowFullScreenInteractive' => 'allowFullScreenInteractive');
}

/**
 * Get an array of where the content is coming from; internal, external, etc.
 * 
 * @return array
 */
function swf_get_xmlurltypes() {
    return array('content' => 'Content Library',
        'fullurl' => 'Full URL http://...',
        'false' => 'None');
}

/**
 * List SMIL, XML, etc. files in swf repository directories
 * searched by /[subdir]/[subdir]/xml/[filename].smil, .xml, etc.
 * @global object $CFG
 * @return array - list of all files in /xml/ directories
 */
function swf_get_contentfiles() {
    global $CFG;
    $swf_content_files = array('false' => get_string('nofile', 'swf'));
    // Default structure = */*/xml/*.*
    foreach (glob($CFG->swf_data_dir.'content/video/*/*.*') as $swf_content_filename) {
        $swf_content_path = str_replace($CFG->swf_data_dir,'', $swf_content_filename);
        $swf_content_files[$swf_content_path] = $swf_content_path;
    }
    return $swf_content_files;
}

/**
 * List SMIL, XML, etc. files in swf repository directories
 * searched by /[subdir]/[subdir]/xml/[filename].smil, .xml, etc.
 * @global object $CFG
 * @return array - list of all files in /xml/ directories
 */
function swf_get_xmlurls() {
    global $CFG;
    $swf_xml_urls = array('false' => get_string('nofile', 'swf'));
    // Default structure = */*/xml/*.*
    foreach (glob($CFG->swf_data_dir.'content/'.$CFG->swf_data_structure) as $swf_xml_filename) {
        //$swf_xml_path_parts = pathinfo($swf_xml_filename);
        $swf_xml_path = str_replace($CFG->swf_data_dir,'',$swf_xml_filename);
        $swf_xml_urls[$swf_xml_path] = $swf_xml_path;
    }
    return $swf_xml_urls;
}

/**
 * List SMIL, XML, etc. files in swfcontent repository directories
 * searched by /[subdir]/config/[subdir]/[filename].smil, .xml, etc.
 * @global object $CFG
 * @return array - list of all files in /xml/ directories
 */
function swf_get_configxmlurls() {
    global $CFG;
    $swf_xml_urls = array('false' => get_string('nofile', 'swf'));
    // Default structure = */config/*/*.*
    foreach (glob($CFG->swf_data_dir.'*/config/*/*.*') as $swf_xml_filename) {
        $swf_xml_path_parts = pathinfo($swf_xml_filename);
        $swf_xml_path = str_replace($CFG->swf_data_dir,'',$swf_xml_filename);
        $swf_xml_urls[$swf_xml_path] = $swf_xml_path;
    }
    return $swf_xml_urls;
}

/**
 * File manager options fileurl
 * Only allow one file to be uploaded/selected. Multiple files must be
 * referenced by single SMIL, XML, etc. files
 * @return array
 */
function swf_get_filemanager_options(){
    return array(
        //'accepted_types'=> array('audio','video','.xml','.smil','.swf','.txt'), // default is '*'
        'maxbytes'=>0,
        'maxfiles'=>1,
        'return_types'=>3,
        'subdirs'=>0
    );
}

/**
 * Sets the main file in fileurl
 * @param object $data
 * @return string
 */
function swf_set_mainfile($data) {
    $filename = null;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->fileurl;
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_swf', 'content', 0, swf_get_filemanager_options());
    }
    $files = $fs->get_area_files($context->id, 'mod_swf', 'content', 0, 'sortorder', false);
    if (count($files) == 1) {
        // only one file attached, set it as main file automatically
        $file = reset($files);
        file_set_sortorder($context->id, 'mod_swf', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
        $filename = $file->get_filename();
    }
    return $filename;
}