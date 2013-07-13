<?php
/** This service handles user info for SWF Activity Module
* WARNING: THIS SERVICE SCRIPT IS EXPERIMENTAL AND NOT SECURE. DO NOT USE ON A PRODUCTION SERVER. THE INSECURE FUNCTIONS HAVE BEEN COMMENTED OUT, JUST IN CASE!
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: view.php,v 0.1 2011/09/25 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* Copyright (C) 2011  Matt Bury
*
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class UserData
{
	private $access;
	private $groups;
	
	public function __construct()
	{
		// Authenticate and check current user's capabilities
		require_once('Access.php');
		$this->access = new Access();
	}
	
	// ------------------------------------------------------------ PUBLIC FUNCTIONS ------------------------------------------------------------ //
	/** Get user by ID
	* 
	* @param obj
	* @return obj
	*/
	public function amf_save_user_data($obj)
	{
		/* 
		$obj['instance'] = 252;
		$obj['swfid'] = 60;
		$obj['userid'] = 2;
		*/
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		/*if ($capabilities->is_logged_in)
		{
			global $CFG;
			global $USER;
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amf_save_user_data', "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			// Create DB object to push into moodle
			$swf_user_data->userid = $USER->id;
			$swf_user_data->courseid = $capabilities->course;
			$swf_user_data->instanceid = $obj['instance'];
			$swf_user_data->swfid = $obj['swfid'];
			$swf_user_data->ipaddress = $_SERVER['REMOTE_ADDR'];
			$swf_user_data->userdata = $obj['capabilities']['avHardwareDisable'].'|'.$obj['capabilities']['hasAccessibility'].'|'.$obj['capabilities']['hasAudio'].'|'.$obj['capabilities']['hasAudioEncoder'].'|'.$obj['capabilities']['hasEmbeddedVideo'].'|'.$obj['capabilities']['hasIME'].'|'.$obj['capabilities']['hasMP3'].'|'.$obj['capabilities']['hasPrinting'].'|'.$obj['capabilities']['hasScreenBroadcast'].'|'.$obj['capabilities']['hasScreenPlayback'].'|'.$obj['capabilities']['hasStreamingAudio'].'|'.$obj['capabilities']['hasStreamingVideo'].'|'.$obj['capabilities']['hasTLS'].'|'.$obj['capabilities']['hasVideoEncoder'].'|'.$obj['capabilities']['isDebugger'].'|'.$obj['capabilities']['language'].'|'.$obj['capabilities']['localFileReadDisable'].'|'.$obj['capabilities']['manufacturer'].'|'.$obj['capabilities']['os'].'|'.$obj['capabilities']['pixelAspectRatio'].'|'.$obj['capabilities']['playerType'].'|'.$obj['capabilities']['screenColor'].'|'.$obj['capabilities']['screenDPI'].'|'.$obj['capabilities']['screenResolutionX'].'|'.$obj['capabilities']['screenResolutionY'].'|'.$obj['capabilities']['version'].'|'.$obj['security']['sandboxType'].'|'.$obj['security']['sandboxType'].'|'.$obj['system']['totalMemory'].'|'.$obj['date']['date'].'|'.$obj['date']['day'].'|'.$obj['date']['fullYear'].'|'.$obj['date']['hours'].'|'.$obj['date']['milliseconds'].'|'.$obj['date']['minutes'].'|'.$obj['date']['month'].'|'.$obj['date']['seconds'].'|'.$obj['date']['time'].'|'.$obj['date']['timezoneOffset'];
			$swf_user_data->timecreated = time();
			$swf_user_data->timemodified = time();
			
			if(insert_record('swf_user_data',$swf_user_data))
			{			
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'User data successfully recorded.';
				return $swf_return;
			} else {
				$swf_return->result = 'NOT_RECORDED';
				$swf_return->message = 'User data not recorded.';
				return $swf_return;
			}
		}*/
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access users.';
		return $swf_return;
	}
	
	// ------------------------------------------------------------ PRIVATE FUNCTIONS ------------------------------------------------------------ //
	/** Convert single DB user record to an object that's safe to send client-side
	* 
	* @param obj
	* @return array
	*/
	private function convert_record_to_object($record)
	{
		global $CFG;
		$result = new object();
		$result->id = $record->id;
		$result->firstname = $record->firstname;
		$result->lastname = $record->lastname;
		$result->fullname = $record->firstname.' '.$record->lastname; // more convenient way to access it
		$result->city = $record->city;
		$result->country = $record->country;
		$result->email = $record->email;
		$result->skype = $record->skype;
		$result->msn = $record->msn;
		$result->yahoo = $record->yahoo;
		$result->aim = $record->aim;
		$result->phone1 = $record->phone1;
		$result->phone2 = $record->phone2;
		$result->url = $records->url;
		$result->timezone = $record->timezone;
		$result->avatar = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f2.jpg'; // path to small user pic
		$result->picture = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f1.jpg'; // path to large user pic
		return $result;
	}
	
	/** Convert DB user records to array that's safe to send client-side
	* 
	* @param obj
	* @return array
	*/
	private function convert_records_to_array($records)
	{
		global $CFG;
		$result = array();
		foreach($records as $record)
		{
				$userData = new object();
				$userData->id = $record->id;
				$userData->firstname = $record->firstname;
				$userData->lastname = $record->lastname;
				$userData->fullname = $record->firstname.' '.$record->lastname; // more convenient way to access it
				$userData->city = $record->city;
				$userData->country = $record->country;
				$userData->email = $record->email;
				$userData->skype = $record->skype;
				$userData->msn = $record->msn;
				$userData->yahoo = $record->yahoo;
				$userData->aim = $record->aim;
				$userData->phone1 = $record->phone1;
				$userData->phone2 = $record->phone2;
				$userData->url = $records->url;
				$userData->timezone = $record->timezone;
				$userData->avatar = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f2.jpg'; // path to small user pic
				$userData->picture = $CFG->wwwroot.'/user/pix.php/'.$record->id.'/f1.jpg'; // path to large user pic
				array_push($result,$userData);
		}
		return $result;
	}
	// -----------------------------------------------------------------------------------------------------------
	
	/** Clean up objects and variables for garbage collector
	* 
	*/
	public function __destruct()
	{
		unset($access);
	}
}
// No, I haven't forgotten the closing PHP tag. It's not necessary here.