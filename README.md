SWF-Activity-Module2.5
======================

SWF Activity Module for Moodle 2.5+

Project Wiki: https://github.com/matbury/SWF-Activity-Module2.5/wiki

Requirements

    Moodle 2.5 or later
    PHP 5.3 or later
    
Please note that the AMFPHP API doesn't work with PHP 5.4 and later. An alternative method to push grades to Moodle have been provided, using HTTP calls and URLVariables. Please see the file /swf/scripts/gradeupdate.php


Installation

1. Upload /swf/ directory and all its contents to /moodle/mod/ 
2. In Moodle, login as administrator
3. Go to Administration > Site administration > Notifications
4. Installation process will initiate (follow the on-screen instructions)

During the installation process, the SWF Activity Module will attempt to move 
the /moodle/mod/swf/swf/ directory to /moodledata/repository/
If this fails, you will have to move the directory and all its contents manually.

For further information about installing activity modules in Moodle see:
http://docs.moodle.org/25/en/Installing_add-ons
