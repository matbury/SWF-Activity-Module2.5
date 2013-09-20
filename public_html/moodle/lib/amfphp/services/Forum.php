<?php
/** This service handles user info for SWF Activity Module
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: view.php,v 0.1 2011/05/01 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* Copyright (C) 2009  Matt Bury
*
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Forum
{
	private $access;
	private $groups;
	
	public function __construct()
	{
		// Authenticate and check current user's capabilities
		require_once('Access.php');
		$this->access = new Access();
		// Groups API
		require_once('Groups.php');
		$this->groups = new Groups();
	}
	
	// ------------------------------------------------------------ PUBLIC FUNCTIONS ------------------------------------------------------------ //
	/** Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
	* 
	* @return object
	*/
	public function amf_ping($obj = null)
	{
		$swf_return->result = 'SUCCESS';
		$swf_return->message = 'Forum.amf_ping successfully called.';
		$swf_return->service_version = '2011.05.01';
		$swf_return->php_version = phpversion(); // get current server PHP version
		return $swf_return;
	}
	
	/** Get forum discussions by ID
	* 
	* @param obj
	* @return obj - List of discussion threads on the selected forum
	*/
	public function amf_get_forum_discussions($obj)
	{
		/* 
		$obj['instance'] = 45;
		$obj['swfid'] = 24;
		$obj['userid'] = 2;
		$obj['forum'] = 1; // The forum instance ID
		*/
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades)
		{
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_forum_discussions id:'.$obj['forum'], "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$discussions = $DB->get_records('forum_discussions','forum',$obj['forum']);
			if($discussions)
			{
				$swf_return->discussions = $discussions;
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Forum discussions successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access forum discussions.';
		return $swf_return;
	}
	
	/** Get forum posts by discussions ID
	* 
	* @param obj
	* @return obj - Contains a 2D (posts x words) array of all words in forum discussion thread
	*/
	public function amf_get_forum_posts($obj)
	{
		/* 
		$obj['instance'] = 45;
		$obj['swfid'] = 24;
		$obj['userid'] = 2;
		$obj['forum'] = 1; // The forum instance on course page
		$obj['discussion'] = 1; // The discussion thread on the forum
		*/
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		if ($capabilities->is_logged_in && $capabilities->view_own_grades)
		{
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp get_forum_posts id:'.$obj['discussion'], "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			$records = $DB->get_records('forum_posts','discussion',$obj['discussion']);
			if($records)
			{
				$i = 0;
				//$posts = array();
				foreach($records as $record)
				{
					$post_obj = new object();
					$post_obj->text = strip_tags($record->message); // remove HTML tags
					$post_obj->words = str_word_count( strtolower($post_obj->text),1); // convert text into array of lower case words, including contractions and hyphenated words
					$swf_return->posts[$i] = $post_obj;
					$i++;
				}
				//$swf_return->posts = $posts;
				$swf_return->result = 'SUCCESS';
				$swf_return->message = 'Forum posts successfully accessed.';
				return $swf_return;
			}
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to access forum posts.';
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