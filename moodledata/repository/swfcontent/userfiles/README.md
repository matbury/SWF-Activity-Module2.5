SWF-Activity-Module2.5
======================

===Saved Files Directory===
The default Saved Files Directory is /userfiles
It is always a subdirectory of /moodledata/repository/swfcontent/
This directory is where users' generated images are saved.
The SWF Activity Module Debugging app can save randomly generated image files
here to test if your server and Moodle configuration are compatible.

You can change which directory the SWF Activity Module saves files to. Here's
an example where for each new academic year, the userfiles directory is changed:
    Create new directory /moodledata/repository/swfcontent/userfiles.2013.09.01/
    Site administration > Plugins > Activity modules > SWF
    Change name of "Saved Files Directory" to /userfiles.2013.09.01
    Save
Now all new files will be saved to
    /moodledata/repository/swfcontent/userfiles.2013.09.01/
Old files linked to in the grade book and in other activities will still be available.
You can also change the new directories to subdirectories this:
    /moodledata/repository/swfcontent/userfiles/2013.09.01/