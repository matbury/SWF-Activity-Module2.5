README

/moodle/mod/swf/swfs/

Project page: https://code.google.com/p/swf-activity-module/

PLEASE NOTE: This directory at /moodle/mod/swf/swfs/ is for dynamic Flash applications only.

Admins can add new Flash apps to this directory and they will automatically be listed on the SWF Activity Module mod_form.php page.

This directory is intended for dynamic SMIL, XML or FlashVars driven applications only, and not for actual learning content. To avoid confusion between applications and content Flash files, please do not put standalone/self contained learning interactions (e.g. Adobe Captivate, Techsmith Camtasia, or Wink) or other Flash content files (e.g. animations, standalone/self contained games) in this directory.

Rule of thumb: If it can run by itself without loading any other files or pulling external data from somewhere, don't put it in here.

To deploy standalone/self contained learning interactions, curriculum developers and teachers should use the swf/mod_form.php page to:
    1. Select the swf/swfs/preloader.swf application
    2. Upload/Select the content Flash file through Moodle's file manager*

The preloader will automatically load, stream, and manage the content file. The preloader can also detect and send grades from loaded content files. See documentation for details: https://code.google.com/p/swf-activity-module/

* A directory must be set up in:
    1. Site Administration > Plugins > Repositories > Manage repositories.
    2. Enable File system and add a directory, e.g. /moodledata/repository/swfcontent/.

This will allow uploading files through Moodle\'s file manager, as well as FTP. These files can then be accessed by Flash apps through /moodle/mod/swf/file.php with the respective paths and filenames passed in through FlashVars, SMIL, and/or XML. This provides native support for XML driven multimedia learning interactions. The SWF Activity Module automatically searches for .xml and .smil files in /moodledata/repository/swfcontent/*/xml/*.xml and *.smil and lists them in swf/mod_form.php.