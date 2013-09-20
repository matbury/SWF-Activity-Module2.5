/** ExpressInstall is a replacement Flash app for use with SWFObject.
*	When SWFObject detects a user has a lower version than is specified
*	this app is embedded in place of the original Flash app/movie.
*	
*	For further details, see: https://code.google.com/p/swfobject/
*	
*	This app is AS3 and therefore requires Flash Player 9.0.0 or later to work.
*
*	You can compile this code with the free and open source
*	Flash/Flex/Haxe IDE: http://flashdevelop.org/ (Unfortunately, only
*	available on Windows).
*	
*    Copyright © 2013  Matt Bury <matt@matbury.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>
**/

package  {
	
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.events.TimerEvent;
	import flash.external.ExternalInterface;
	import flash.net.navigateToURL;
	import flash.net.URLRequest;
	import flash.system.Capabilities;
	import flash.text.*;
	import flash.utils.Timer;
	
	public class ExpressInstall extends Sprite {
		
		private var _tf:TextField; // Shows user's Flash Player, system, and browser info
		private var _timefield:TextField; // Countdown display
		private var _upgrade:Sprite; // "Upgrade now" button
		private var _timer:Timer;
		private var _time:int = 30; // Countdown seconds 

		public function ExpressInstall() {
			stage.scaleMode = StageScaleMode.NO_SCALE;
			stage.align = StageAlign.TOP_LEFT;
			stage.addEventListener(Event.RESIZE, resize);
			// Initialise app contents
			initTextField();
			initFlashPlayerInfo();
			initUpgradeButton();
			initUpgradeTimer();
			resize(null); // Adjust contents to fit Flash Player window
		}
		
		// If the Flash Player window is resized, adjust contents to match
		private function resize(event:Event):void {
			if(_tf) {
				_tf.width = stage.stageWidth;
				_tf.height = stage.stageHeight;
			}
			if(_timefield) {
				_timefield.x = 2;
				_timefield.y = stage.stageHeight - (_upgrade.height + 2);
			}
			if(_upgrade) {
				_upgrade.x = stage.stageWidth * 0.5 - (_upgrade.width * 0.5);
				_upgrade.y = stage.stageHeight - (_upgrade.height + 2);
			}
		}
		
		// Create text field to display user's Flash Player, system, and browser info
		private function initTextField():void {
			var f:TextFormat = new TextFormat("Trebuchet MS",16,0,false);
			f.align = TextFormatAlign.CENTER;
			_tf = new TextField();
			_tf.defaultTextFormat = f;
			_tf.multiline = true;
			_tf.wordWrap = true;
			_tf.text = "Hello";
			addChild(_tf);
			_timefield = new TextField();
			_timefield.defaultTextFormat = f;
			_timefield.autoSize = TextFieldAutoSize.LEFT;
			_timefield.text = "Opening upgrade page in " + _time + "...";
			addChild(_timefield);
		}
		
		// Display user's Flash Player, system, and browser info
		private function initFlashPlayerInfo():void {
			_tf.text = "Your Flash Player needs upgrading.";
			if(this.root.loaderInfo.parameters.swfversion) {
				_tf.appendText("\nRequired version: " + this.root.loaderInfo.parameters.swfversion);
			}
			_tf.appendText("\nYour current version: " + Capabilities.version);
			_tf.appendText("\nYour operating system: " + Capabilities.os);
			_tf.appendText("\nYour browser: " + String(ExternalInterface.call("function(){return navigator.userAgent}")));
		}
		
		// Create "Upgrade now" button
		private function initUpgradeButton():void {
			var f:TextFormat = new TextFormat("Trebuchet MS",14,0xFFFFFF,true);
			var label:TextField = new TextField();
			label.defaultTextFormat = f;
			label.autoSize = TextFieldAutoSize.LEFT;
			label.text = " Upgrade now ";
			label.selectable = false;
			_upgrade = new Sprite();
			_upgrade.addEventListener(MouseEvent.MOUSE_UP, upgrade);
			_upgrade.graphics.beginFill(0xBB0000,1);
			_upgrade.graphics.drawRect(0,0,label.width,label.height);
			_upgrade.graphics.endFill();
			_upgrade.x = stage.stageWidth - _upgrade.width;
			_upgrade.buttonMode = true;
			_upgrade.mouseChildren = false;
			_upgrade.addChild(label);
			addChild(_upgrade);
		}
		
		// Start countdown to automatically open upgrade/download page
		private function initUpgradeTimer():void {
			_timer = new Timer(1000,_time);
			_timer.addEventListener(TimerEvent.TIMER, timerTick);
			_timer.addEventListener(TimerEvent.TIMER_COMPLETE, timerComplete);
			_timer.start();
		}
		
		// Show countdown
		private function timerTick(event:TimerEvent):void {
			_time--;
			_timefield.text = "Opening upgrade page in " + _time + "...";
		}
		
		// Countdown complete so run upgrade
		private function timerComplete(event:TimerEvent):void {
			_timer.removeEventListener(TimerEvent.TIMER, timerTick);
			_timer.removeEventListener(TimerEvent.TIMER_COMPLETE, timerComplete);
			upgrade(null);
		}
		
		// Open Flash Player upgrade/download page at Adobe.com
		private function upgrade(event:MouseEvent):void {
			_timer.stop();
			var request:URLRequest = new URLRequest("https://get.adobe.com/flashplayer/");
			navigateToURL(request,"_blank"); // Open page in a new window/tab
			_timefield.text = "Opened upgrade page";
		}
	}
} // End of code.
