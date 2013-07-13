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

//require_once("$CFG->libdir/filelib.php");

/**
 * ######################## moodle/mod/swf/mod_form.php ########################
 */
    /**
    * Get an array with Flash Params stage scale mode options
    *
    * @return array
    */
    /*function swf_get_scale(){
      return array('noScale' => 'noScale',
          'showAll' => 'showAll',
          'exactFit' => 'exactFit',
          'noBorder' => 'noBorder');
    }*/

    /**
    * Get an array with Flash Params stage align options
    *
    * @return array
    */
    /*function swf_get_salign(){
      return array('T' => 'top-center',
          'B' => 'bottom-center',
          'L' => 'center-left',
          'R' => 'center-right',
          'TL' => 'top-left',
          'TR' => 'top-right',
          'BL' => 'bottom-left',
          'BR' => 'bottom-right');
    }*/
    
    /**
    * Get an array with general true/false options
    *
    * @return array
    */
    /*function swf_get_truefalse(){
      return array('true' => 'true',
          'false' => 'false');
    }*/

    /**
    * Get an array with Flash Params allowscriptaccess options
    *
    * @return array
    */
    /*function swf_get_allowscriptaccess(){
      return array('always' => 'always',
          'sameDomain' => 'sameDomain',
          'never' => 'never');
    }*/

    /**
    * Get an array with Flash Params allownetworking options
    *
    * @return array   
    */
    /*function swf_get_allownetworking(){
      return array('all' => 'all',
          'internal' => 'internal',
          'none' => 'none');
    }*/

    /**
    * Get an array with Flash Params allowfullscreen options
    *
    * @return array   
    */
    /*function swf_get_fullscreen_options(){
      return array('false' => 'false',
          'allowFullScreen' => 'allowFullScreen',
          'allowFullScreenInteractive' => 'allowFullScreenInteractive');
    }*/
    
    /**
     * 
     * @global type $CFG
     * @param type $swf
     * @param type $context
     * @return type string An absolute URL to the XML or SMIL file
     */
    /*function swf_get_fileurl($swf, $context){
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
    }*/
    
    /**
     * Get moodle server
     * @global type $CFG
     * @return boolean|string    myserver.com:port
     */
    /*function swf_get_server() {
        global $CFG;

        if (!empty($CFG->wwwroot)) {
            $url = parse_url($CFG->wwwroot);
        }

        if (!empty($url['host'])) {
            $hostname = $url['host'];
        } else if (!empty($_SERVER['SERVER_NAME'])) {
            $hostname = $_SERVER['SERVER_NAME'];
        } else if (!empty($_ENV['SERVER_NAME'])) {
            $hostname = $_ENV['SERVER_NAME'];
        } else if (!empty($_SERVER['HTTP_HOST'])) {
            $hostname = $_SERVER['HTTP_HOST'];
        } else if (!empty($_ENV['HTTP_HOST'])) {
            $hostname = $_ENV['HTTP_HOST'];
        } else {
            notify('Warning: could not find the name of this server!');
            return false;
        }
        if (!empty($url['port'])) {
            $hostname .= ':'.$url['port'];
        } else if (!empty($_SERVER['SERVER_PORT'])) {
            if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
                $hostname .= ':'.$_SERVER['SERVER_PORT'];
            }
        }
        return $hostname;
    }*/
    
    /**
     * File manager options for mod/swf/mod_form.php
     * @return array
     */
    /*function swf_get_filemanager_options(){
        $filemanager_options = array(
            //'accepted_types'=> array('audio','video','.xml','.smil','.swf','.txt'), // default is '*'
            'maxbytes'=>0,
            'maxfiles'=>1,
            'return_types'=>3,
            'subdirs'=>0
        );
        return $filemanager_options;
    }*/
    
    /**
     * Sets the main file in fileurl
     * @param object $data
     * @return string
     */
    /*function swf_set_mainfile($data) {
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
    }*/
