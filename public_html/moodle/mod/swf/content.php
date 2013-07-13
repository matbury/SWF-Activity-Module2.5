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
    require_once('../../lib/filelib.php'); // contains mimeinfo() and send_file() definitions
    // Don't use Moodle required_param() to avoid sending any HTML messages to Flash apps
    
    require_login(); // Users must be logged in to access files

    global $CFG;

    $swf_relative_path = get_file_argument(); // gets the appended URL e.g. /dir/subdir/file.jpg
    $swf_ok = false;
    if(strrpos($swf_relative_path,'.') > strlen($swf_relative_path) - 6) {
        // Strip out special characters, extra slashes, and parent directory stuff
        $swf_disallowed = array('../','\'','\"','`',':','#','{','}','<','>','*','=','!','?','\\','//','///');
        $swf_replace = array('','','','','','','','','','','','','','','','/','/');
        $swf_relative_path = str_replace($swf_disallowed,$swf_replace,$swf_relative_path);
        $swf_full_path = $CFG->dataroot.$CFG->swf_content_dir.$swf_relative_path;
        if(file_exists($swf_full_path) && is_readable($swf_full_path)) {
            $swf_path_info = pathinfo($swf_full_path);
            $swf_mime_type = mimeinfo('type', $swf_path_info['basename']);
            send_file($swf_full_path,$swf_path_info['basename'],'default',0,false,false,$swf_mime_type,false);
            exit;
        }
    }
    header('HTTP/1.0 404 Not Found'); // Send back a 404 so that apps don't wait for a timeout
    exit('404 Error: File not found'); // Pure text output - Flash app friendly

/*print_object($swf_data_info);
echo $swf_mime_type;
echo '<br/>extension: '.$swf_data_info['extension'];
echo '<br/>relative path: '.$swf_relative_path;
echo '<br/>full path: '.$swf_data_path;
echo '<br/>file name: '.$swf_data_info['basename'];*/

// If no value is provided in $_GET, we get an empty string.
// See: http://ca2.php.net/manual/en/reserved.variables.get.php
/*if($_GET['file'] === '')
{
    header('HTTP/1.0 404 Not Found'); // Send back a 404 so that apps don't wait for a timeout
    exit('404 Error: File not found'); // Pure text output - Flash app friendly
} else {
    // Strip out special characters, extra slashes, and parent directory stuff
    $swf_disallowed = array('../','\'','\"',':','{','}','*','&','=','!','?','\\','//','///');
    $swf_replace = array('','','','','','','','','','','','','/','/');
    $swf_file_path = str_replace($swf_disallowed,$swf_replace,$_GET['file']);
}

if(strrpos($swf_file_path,'.') > strlen($swf_file_path) - 6) {
    $swf_path_info = pathinfo($swf_file_path);
    $swf_file_extention = strtolower($swf_path_info['extension']);
    $swf_file_name = $swf_path_info['basename'];
    $swf_file_path = $CFG->dataroot.$CFG->swf_content_dir.$swf_file_path;
    // e.g. /home/sites/example.com/moodledata + /repository/swfcontent/ + subdir/myimage.jpg
} else {
    header('HTTP/1.0 404 Not Found'); // Send back a 404 so that apps don't wait for a timeout
    exit('404 Error: File not found'); // Pure text output - Flash app friendly
}

// Moodle file serving...
$swf_mime_type = mimeinfo('type', $swf_file_name);
send_file($swf_file_path,$swf_file_name,'default',0,false,false,$swf_mime_type,false);
//From: lib/filelib.php::send_file($path, $filename, $lifetime = 'default', $filter=0, $pathisstring=false, $forcedownload=false, $mimetype='', $dontdie=false)
exit;*/

// Restrict to Flash compatible MIME types
/*$swf_mime_type = false;
if(file_exists($swf_file_path) && is_readable($swf_file_path)) {
    // set the MIME type
    switch ($swf_file_extention)
    {

        // AUDIO
        case 'aac':
        //$swf_mime_type = 'audio/mp4';
        $swf_mime_type = 'audio/aac'; // = Moodle
        break;

        case 'f4a':
        $swf_mime_type = 'video/mp4'; // = Moodle
        break;

        case 'm4a':
        //$swf_mime_type = 'video/mp4';
        $swf_mime_type = 'audio/mp4'; // = Moodle
        break;

        case 'mp3':
        //$swf_mime_type = 'audio/mpeg';
        $swf_mime_type = 'audio/mp3'; // = Moodle
        break;

        //IMAGE
        case 'gif':
        $swf_mime_type = 'image/gif';
        break;

        case 'jpeg':
        $swf_mime_type = 'image/jpeg';
        break;

        case 'jpg':
        $swf_mime_type = 'image/jpeg';
        break;

        case 'png':
        $swf_mime_type = 'image/png';
        break;

        // TEXT
        case 'smil':
        $swf_mime_type = 'text/xml';
        break;

        case 'srt':
        $swf_mime_type = 'text/plain';
        break;

        case 'txt':
        $swf_mime_type = 'text/plain';
        break;

        case 'xml':
        $swf_mime_type = 'text/xml';
        break;

        // VIDEO - Don't work in Flash apps! ###################################
        case 'f4v':
        //$swf_mime_type = 'video/x-flv';
        $swf_mime_type = 'video/mp4'; // = Moodle
        break;

        case 'flv':
        $swf_mime_type = 'video/x-flv'; // = Moodle
        break;

        case 'm4v':
        //$swf_mime_type = 'video/x-m4v';
        $swf_mime_type = 'video/mp4'; // = Moodle
        break;

        case 'mov':
        $swf_mime_type = 'video/mp4';
        //$swf_mime_type = 'video/quicktime'; // = Moodle (forces download)
        break;

        case 'mp4':
        $swf_mime_type = 'video/mp4'; // = Moodle
        break;
        // #####################################################################

        // FLASH
        case 'swf':
        $swf_mime_type = 'application/x-shockwave-flash'; // = Moodle
        break;

        default: 
        $swf_mime_type = false;
    }
}

// if a valid MIME type exists, display the image by sending appropriate headers and streaming the file
if ($swf_mime_type) {
    header('Content-type: '.$swf_mime_type);
    //header('Content-Transfer-Encoding: binary');
    header('Content-disposition: inline; filename='.$swf_file_name); // Set the file name
    //header('Content-length: '.(string)filesize($swf_file_path)); // Slows things down?
    // Avoid caching problems
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 06 Jul 2013 16:02:00 GMT"); // Date in the past
    $file = @ fopen($swf_file_path, 'rb'); // 'rb' = read only, binary mode
    if ($file) {
        ob_end_clean(); // Prevent large files from exceeding php.ini memory limit
        fpassthru($file); // Send file
        fclose($file);
        exit;
    }
} else {
    header('HTTP/1.0 404 Not Found'); // Send back a 404 so that apps don't wait for a timeout
    exit('404 Error: File not found'); // Pure text output - Flash app friendly
}*/
// Closing PHP tag may cause whitespace problems!