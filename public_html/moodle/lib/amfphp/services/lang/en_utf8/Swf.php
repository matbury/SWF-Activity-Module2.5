<?php
/** This utility class holds language strings
* 
*
* @author Matt Bury - matbury@gmail.com
* @version $Id: Swf.php,v 0.2 2011/09/14 matbury Exp $
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

class Swf
{
	
	/*
	private $AMF_ENABLED;
	private $AMF_ERROR;
	private $AMF_FAILED;
	private $AMF_FILE_NOT_WRITTEN;
	private $AMF_GRADE_SENT;
	private $AMF_GRADE_NOT_SENT;
	private $AMF_LOGGED_IN;
	private $AMF_NO_GRADE_ITEM;
	private $AMF_NO_PERMISSION;
	private $AMF_NOT_ENABLED;
	private $AMF_NOT_LOGGED_IN;
	private $AMF_SUCCESS;
	*/
	
	public function __construct()
	{
		// do nothing
	}
	
	/**
	 * Returns a result code (string) and a user message (string)
	 * @param (object) - null
	 * @return (object) - result (string) and message (string)
	 */
	public function AMF_ENABLED($obj = null)
	{
		$obj = new object();
		$obj->message = 'AMF is enabled.';
		$obj->result = 'AMF_ENABLED';
		return $obj;
	}
	
	/**
	 * Returns a result code (string) and a user message (string)
	 * @param (object) - null
	 * @return (object) - result (string) and message (string)
	 */
	public function AMF_ERROR($obj = null)
	{
		$obj = new object();
		$obj->message = 'There was an unknown error on the server.';
		$obj->result = 'AMF_ERROR';
		return $obj;
	}
	
	// -----------------------------------------------------------------------------------------------------------
	
	/**
	* Clean up objects and variables for garbage collector
	*
	*/
	public function __destruct()
	{
		//unset($ENABLED);
	}
}
// No, I haven't forgotten the closing PHP tag. It's not necessary here.