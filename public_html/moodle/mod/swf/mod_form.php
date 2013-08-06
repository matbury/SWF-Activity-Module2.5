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

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('modformlib.php');

/**
 * Module instance settings form
 */
class mod_swf_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB, $USER;
        
        $mform = $this->_form;
        
        // Give info on when instance was created and modified
        $cmid = optional_param('update',0,PARAM_INT);
        if($cmid !== 0) {
            $swf_module = get_coursemodule_from_id('swf',$cmid); // get course module instance
            $params = array('id'=>$swf_module->instance);
            $swf = $DB->get_record('swf',$params); // get swf instance
            $mform->addElement('header','modifiedby',get_string('usermodified','swf'));
            $swf_created = get_string('timecreated','swf').' '.date('H:i Y-m-d',usertime($swf->timecreated));
            $swf_modified = get_string('usermodified','swf').' '.$USER->firstname.' '.$USER->lastname.' '.date('H:i Y-m-d',usertime($swf->timemodified));
            $mform->addElement('html',$swf_created.'<br/>'.$swf_modified);
        }
        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('name'), array('size'=>64));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();
        
        /*
         * Flash App and Flash Player embed parameters
         */
        $mform->addElement('header', 'contentsection', get_string('swffile', 'swf'));
        $mform->addHelpButton('contentsection', 'swffile', 'swf');
        // Flash app
        $mform->addElement('select', 'swfurl', get_string('swffile', 'swf'), swf_get_swfs(), '');
        $mform->setDefault('swfurl', '');
        $mform->setType('swfurl', PARAM_TEXT);
        $mform->addHelpButton('swfurl', 'swffile', 'swf');
        // Flash plugins (Strobe and JW Player)
        $mform->addElement('select', 'plugin', get_string('plugin', 'swf'), swf_get_plugins(), '');
        $mform->setDefault('plugin', '');
        $mform->setType('plugin', PARAM_TEXT);
        $mform->addHelpButton('plugin', 'plugin', 'swf');
        // Flash Player embed parameters
        $mform->addElement('text', 'width', get_string('width', 'swf'), array('size'=>5));
        $mform->setType('width', PARAM_TEXT);
        $mform->setDefault('width', '100%');
        $mform->addElement('text', 'height', get_string('height', 'swf'), array('size'=>5));
        $mform->setType('height', PARAM_TEXT);
        $mform->setDefault('height', '95%');
        $mform->addElement('text', 'version', get_string('version', 'swf'), array('size'=>5));
        $mform->setType('version', PARAM_TEXT);
        $mform->setDefault('version', '11.4.0');
        $mform->addElement('select', 'allowfullscreen', get_string('allowfullscreen', 'swf'), swf_get_fullscreen_options());
        $mform->setDefault('allowfullscreen', 'allowFullScreenInteractive');
        $mform->addElement('select', 'scale', get_string('scale', 'swf'), swf_get_scale());
        $mform->setDefault('scale', 'noScale');
        $mform->addElement('select', 'salign', get_string('salign', 'swf'), swf_get_salign());
        $mform->setDefault('salign', 'TL');
        $mform->addElement('text', 'pagecolor', get_string('pagecolor', 'swf'), array('size'=>6));
        $mform->setType('pagecolor', PARAM_TEXT);
        $mform->setDefault('pagecolor', 'FFFFFF');
        $mform->addElement('text', 'bgcolor', get_string('bgcolor', 'swf'), array('size'=>6));
        $mform->setType('bgcolor', PARAM_TEXT);
        $mform->setDefault('bgcolor', 'FFFFFF');
        $mform->addElement('select', 'seamlesstabbing', get_string('seamlesstabbing', 'swf'), swf_get_truefalse());
        $mform->setDefault('seamlesstabbing', 'true');
        $mform->addElement('select', 'allowscriptaccess', get_string('allowscriptaccess', 'swf'), swf_get_allowscriptaccess());
        $mform->setDefault('allowscriptaccess', 'sameDomain');
        $mform->addElement('select', 'allownetworking', get_string('allownetworking', 'swf'), swf_get_allownetworking());
        $mform->setDefault('allownetworking', 'all');
        
        /*
         * Learning interaction data: FlashVars, XML files, uploaded SWFs, and external links
        */
        $mform->addElement('header', 'header_swf', get_string('header_swf', 'swf'));
        // Search dataroot for XML and SMIL files
        // xmlurl to moodledata/repository/swfcontent/*/*/xml/[filename].xml and .smil
        $mform->addElement('select', 'xmlurl', get_string('xmlurl', 'swf'), swf_get_xmlurls());
        $mform->setType('xmlurl', PARAM_TEXT);
        $mform->addHelpButton('xmlurl', 'xmlurl', 'swf');
        $mform->setDefault('xmlurl', '');
        // xmlurlname FlashVar name for xmlurl
        $mform->addElement('text', 'xmlurlname', get_string('xmlurlname', 'swf'), array('size'=>12));
        $mform->setType('xmlurlname', PARAM_TEXT);
        $mform->addRule('xmlurlname', null, 'required', null, 'client');
        $mform->addHelpButton('xmlurlname', 'xmlurlname', 'swf');
        $mform->setDefault('xmlurlname', 'xmlurl');
        // Uploaded SMIL, XML or Flash file
        $mform->addElement('filemanager', 'fileurl', get_string('fileurl', 'swf'), array('optional'=>true), swf_get_filemanager_options());   
        $mform->addHelpButton('fileurl', 'fileurl', 'swf');
        // Exit URL
        $mform->addElement('text', 'exiturl', get_string('exiturl', 'swf'), array('size'=>75));
        $mform->setType('exiturl', PARAM_RAW_TRIMMED); // remove whitespace
        $mform->addHelpButton('exiturl', 'exiturl', 'swf');
        $mform->setDefault('exiturl', '');
        // Exit URL FlashVar name for exiturl
        $mform->addElement('text', 'exiturlname', get_string('exiturlname', 'swf'), array('size'=>12));
        $mform->setType('exiturlname', PARAM_TEXT);
        $mform->addRule('exiturlname', null, 'required', null, 'client');
        $mform->addHelpButton('exiturlname', 'exiturlname', 'swf');
        $mform->setDefault('exiturlname', 'exiturl');
        // Name value pairs
        $swf_name_settings = array('size'=>20);
        $swf_value_settings = 'wrap="virtual" rows="3" cols="72"';
        $mform->addElement('text', 'name1', get_string('namepair', 'swf'), $swf_name_settings);
        $mform->setType('name1', PARAM_TEXT);
        $mform->addHelpButton('name1', 'namepair', 'swf');
        $mform->addElement('textarea', 'value1', get_string("valuepair", "swf"), $swf_value_settings);
        $mform->setType('value1', PARAM_TEXT);
        $mform->addElement('text', 'name2', get_string('namepair', 'swf'), $swf_name_settings);
        $mform->setType('name2', PARAM_TEXT);
        $mform->addElement('textarea', 'value2', get_string("valuepair", "swf"), $swf_value_settings);
        $mform->setType('value2', PARAM_TEXT);
        $mform->addElement('text', 'name3', get_string('namepair', 'swf'), $swf_name_settings);
        $mform->setType('name3', PARAM_TEXT);
        $mform->addElement('textarea', 'value3', get_string("valuepair", "swf"), $swf_value_settings);
        $mform->setType('value3', PARAM_TEXT);
        // API Key
        $mform->addElement('textarea', 'apikey', get_string('apikey', 'swf'), $swf_value_settings);
        $mform->setType('apikey', PARAM_TEXT);
        $mform->addHelpButton('apikey', 'apikey', 'swf');
        $mform->setDefault('apikey', '');
        // API Key FlashVar name for apikey
        $mform->addElement('text', 'apikeyname', get_string('apikeyname', 'swf'), array('size'=>12));
        $mform->setType('apikeyname', PARAM_TEXT);
        $mform->addRule('apikeyname', null, 'required', null, 'client');
        $mform->addHelpButton('apikeyname', 'apikeyname', 'swf');
        $mform->setDefault('apikeyname', 'apikey');
        // Config XML 
        $mform->addElement('select', 'configxml', get_string('configxml', 'swf'), swf_get_configxmlurls());
        $mform->setType('configxml', PARAM_TEXT);
        $mform->addHelpButton('configxml', 'configxml', 'swf');
        $mform->setDefault('configxml', '');
        // Config URL FlashVar name for configxml
        $mform->addElement('text', 'configxmlname', get_string('configxmlname', 'swf'), array('size'=>12));
        $mform->setType('configxmlname', PARAM_TEXT);
        $mform->addRule('configxmlname', null, 'required', null, 'client');
        $mform->addHelpButton('configxmlname', 'configxmlname', 'swf');
        $mform->setDefault('configxmlname', 'configxml');

        //-------------------------------------------------------------------------------
        $this->standard_grading_coursemodule_elements();
        // Insert instructions of how to manage grades
        $mform->addElement('html', '<p>&nbsp;</p><p><strong>'.get_string('gradeeditintro', 'swf').'</strong></p>
            <ol>
                <li>'.get_string('gradeedit1', 'swf').'</li>
                <li>'.get_string('gradeedit2', 'swf').'</li>
                <li>'.get_string('gradeedit3', 'swf').'</li>
                <li>'.get_string('gradeedit4', 'swf').'</li>
                <li>'.get_string('gradeedit5', 'swf').'</li>
            </ol>
            <p>'.get_string('gradeedit6', 'swf').'</p>');
        
        // Add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // Add standard buttons, common to all modules
        $this->add_action_buttons();
    }
    
    /**
     * Overrides method from moodleform_mod (moodleform_mod.php)
     * 
     * TODO - If filemanager is empty, don't push anything to DB
     * 
     * @param type $default_values
     */
    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('fileurl');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_swf', 'content', 0, swf_get_filemanager_options()); 
            $default_values['fileurl'] = $draftitemid;
        }
    }
    
    /**
     * Overrides method from moodleform_mod (moodleform_mod.php)
     * @global type $USER
     * @param type $data
     * @param type $files
     * @return type
     */
    public function validation($data, $files) {
        global $USER;
        //print_object($data); // Everything the form has sent
        $errors = parent::validation($data, $files);
        if($data['xmlurl'] === 'true') {
            $usercontext = get_context_instance(CONTEXT_USER, $USER->id);
            $fs = get_file_storage();
            if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['fileurl'], 'sortorder, id', false)) {
                $errors['fileurl'] = get_string('required');
                return $errors;
            }
            if (count($files) == 1) {
                //print_object($files); // Everything about the file
                // no need to select main file if only one picked
                return $errors;
            } else if(count($files) > 1) {
                $mainfile = false;
                foreach($files as $file) {
                    if ($file->get_sortorder() == 1) {
                        $mainfile = true;
                        break;
                    }
                }
                // set a default main file
                if (!$mainfile) {
                    $file = reset($files);
                    file_set_sortorder($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                                       $file->get_filepath(), $file->get_filename(), 1);
                }
            }
        }
        return $errors;
    }
}
