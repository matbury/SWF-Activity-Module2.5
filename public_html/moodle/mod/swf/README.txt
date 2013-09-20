Installation

There are 3 main stages to installing the SWF Activity Module:

    Create a File system repository
    Install the AMFPHP interface and services
    Install the SWF Activity Module

1. Create a File system repository

    Via FTP, create or upload a directory at /moodledata/repository/swfcontent/
    Login to Moodle as an admin
    Go to Site administration > Plugins > Repositories > Manage repositories
    Check that File system is "enabled and visible"
    In File system, go to "Settings" and "Create a repository instance"
    Select "swfcontent" from the list, give it a name, and save it
    That's it!

A creative commons licensed multimedia learning content cartridge is supplied for testing and as an example to get you started. To install it:

    Via FTP, upload the /commonobjects/ directory and all its contents to /moodledata/repository/swfcontent/mmlcc/ so that the path is /moodledata/repository/swfcontent/mmlcc/commonobjects/

For further details about file system repositories see: http://docs.moodle.org/25/en/File_system_repository

2. Install the AMFPHP interface and services

    Via FTP, upload the /amfphp/ directory and all its contents to /moodle/lib/ so that the path is /moodle/lib/amfphp/
    That's it!

3. Install the SWF Activity Module

    Via FTP, upload the /swf/ directory and all its contents to /moodle/mod/ so that the path is /moodle/mod/swf/
    Login as an administrator
    Go to Site administration > Notifications and the module will install
    You be taken to Site administration > Plugins > Activity modules > SWF
    Click save
    That's it!

For further information about installing activity modules in Moodle see: http://docs.moodle.org/25/en/Installing_add-ons
