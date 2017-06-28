# Custom string manager for Moodle

This plugin will override core_string_manager_standard to display two strings, English and simplified Chinese Mandarin, side by side. 

## Installation

1. Install this plugin

        $ cd /path/to/your/moodle/dirroot
        $ cd local
        $ git clone git@github.com:iandavidwild/moodle-local_duallang.git duallang

2. Edit your main `config.php` and add the following line there. Of course, you have to add this line before the `setup.php` is
   included at the end of the file.

        $CFG->customstringmanager = '\local_stringman\duallang_string_manager';

## More documentation

See https://tracker.moodle.org/browse/MDL-49361 for more details
