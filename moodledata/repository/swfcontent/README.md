SWF-Activity-Module2.5
======================

===Multimedia Content Directory===
The default Multimedia Content Directory is /moodledata/repository/swfcontent
It is always a subdirectory of /moodledata/repository
This directory is where libraries of multimedia, XML, and SMIL files are stored for use by learning apps deployed by the SWF Activity Module.

You can change which directory the SWF Activity Module uses as its Multimedia
Content Directory like this:
    Create new directory /moodledata/repository/new_swfcontent
    Site administration > Plugins > Activity modules > SWF
    Change name of "Multimedia Content Directory" to /moodledata/repository/new_swfcontent
    Save
Now the SWF Activity Module will direct apps to this new directory to load learning content from:
    /moodledata/repository/new_swfcontent


===Multimedia Content Directory Schema===
The SWF Activity Module instance configuration form (mod/swf/mod_form.php) automatically searches for and lists all files placed in a particular hierarchy in the Multimedia Content Directory.
Content packages should follow this schema to be made available on the instance configuration form:
    /moodledata/repository/swfcontent/[namespace]/[packagename]/xml/
The example package Common objects demonstrates this schema:
    /moodledata/repository/swfcontent/matbury/commonobjects/xml/common_objects.smil
My namespace is: matbury (I use my website URL: http://matbury.com/)
The multimedia content package name is: commonobjects
So the instance configuration form finds and lists /matbury/commonobjects/xml/common_objects.smil
More than one SMIL/XML file can be listed from each /xml/ directory.