<?php
/** This service handles messages for SWF Activity Module
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: Message.php,v 0.2 2010/02/19 matbury Exp $
* @licence http://www.gnu.org/copyleft/gpl.html GNU Public Licence
* Copyright (C) 2010  Matt Bury
*
* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Message
{
	private $access;
	private $users;
	
	public function __construct()
	{
		// Check current user's capabilities
		require_once('Access.php');
		$this->access = new Access();
		// Get user's profile details
		require_once('Users.php');
		$this->users = new Users();
	}
	
	// ------------------------------------------------------------ PUBLIC FUNCTION ------------------------------------------------------------ //
	/** Test gateway - a simple 'ping' function to test that NetConnection.connect() has been correctly established
	* 
	* @return string
	*/
	public function amf_ping($obj = null)
	{
		$swf_return->result = 'SUCCESS';
		$swf_return->message = 'Message.amf_ping successfully called.';
		$swf_return->service_version = '2010.02.19';
		$swf_return->php_version = phpversion(); // get current server PHP version
		return $swf_return;
	}
	
	/**
	* Calls moodle/message/lib.php::message_post_message()
	*
	* @param instance (int) - Moodle module instance
	* @param swfid (int) - SWF Activity Module instance
	* @param userto (int) - User ID to send message to
	* @param usermessage (string) - The message
	* 
	* @return (int) - ID of message or false (boolean) on error
	*/
	public function amf_post_message($obj)
	{
		$obj['instance'] = 27;
		$obj['swfid'] = 12;
		$obj['userto'] = 4;
		$obj['usermessage'] = '<p>This is a paragraph tag</p>
		<div>This is a div tag</div>';
		// Get current user's capabilities
		$capabilities = $this->access->get_capabilities($obj['instance'],$obj['swfid']);
		// If there was a problem with authentication, return the error message
		if(!empty($capabilities->error))
		{
			return $capabilities->error;
		}
		// Make sure they have permission to call this function
		if ($capabilities->is_logged_in && $capabilities->view_own_grades)
		{
			global $CFG;
			global $USER;
			
			require_once('../../../message/lib.php');
			
			// Add view to Moodle log
			add_to_log($capabilities->course, 'swf', 'amfphp message_post_message userid:'.$obj['userto'], "view.php?id=$capabilities->cmid", "$capabilities->swfname"); 
			
			// Check that messaging is enabled
			if (empty($CFG->messaging)) {
 				return 'Messaging is disabled on this site';
 			}
			//
			$userfrom->id = $USER->id;
			$userto->id = $obj['userto'];
			$message = (string) $obj['usermessage'];
			$format = FORMAT_HTML;
			$messagetype = 'direct';
			//
			$swf_return->records = message_post_message($userfrom, $userto, $message, $format, $messagetype);
			$swf_return->result = 'SUCCESS';
			$swf_return->message = 'Message successfully sent.';
			return $swf_return;
		}
		$swf_return->result = 'NO_PERMISSION';
		$swf_return->message = 'You do not have permission to send messages.';
		return $swf_return;
	}
	
	// -----------------------------------------------------------------------------------------------------------
	
	/**
	* Clean up objects and variables for garbage collector
	*
	*/
	public function __destruct()
	{
		unset($access);
		unset($users);
	}
}
// No, I haven't forgotten the closing PHP tag. It's not necessary here.