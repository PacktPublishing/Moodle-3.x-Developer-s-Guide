<?php
// This file is part of the Kamedia GPS course format for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains main class for the GPS Format free course format.
 *
 * @since     2.0
 * @package   format_location
 * @copyright 2013 Barry Oosthuizen
 * @author    2013 Barry Oosthuizen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/course/editsection_form.php');
define('FORMAT_LOCATION_RESTRICTED', 1);
define('FORMAT_LOCATION_UNRESTRICTED', 0);

/**
 * Main class for the Topics course format
 *
 * @package    format_location
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_location extends format_base {

    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string) $section->name !== '') {
            return format_string($section->name, true, array('context' => context_course::instance($this->courseid)));
        } else if ($section->section == 0) {
            return get_string('section0name', 'format_location');
        } else {
            return get_string('topic') . ' ' . $section->section;
        }
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
		$usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            $url->param('section', $sectionno);
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
        return $ajaxsupport;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Check if there are callbacks to extend course navigation.
        parent::extend_course_navigation($navigation, $node);
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array('search_forums', 'news_items', 'calendar_upcoming', 'recent_activity')
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Topics format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT,
                ),
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numberweeks'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            //COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                )
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'topics', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;

        if ($oldcourse !== null) {
            $data = (array) $data;
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        // If previous format does not have the field 'numsections'
                        // and $data['numsections'] is not set,
                        // we fill it with the maximum section number from the DB.
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default.
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        return $this->update_format_options($data);
    }

    /**
     * Definitions of the additional options that this course format uses for section
     *
     * See {@link format_base::course_format_options()} for return array definition.
     *
     * Additionally section format options may have property 'cache' set to true
     * if this option needs to be cached in {@link get_fast_modinfo()}. The 'cache' property
     * is recommended to be set only for fields used in {@link format_base::get_section_name()},
     * {@link format_base::extend_course_navigation()} and {@link format_base::get_view_url()}
     *
     * For better performance cached options are recommended to have 'cachedefault' property
     * Unlike 'default', 'cachedefault' should be static and not access get_config().
     *
     * Regardless of value of 'cache' all options are accessed in the code as
     * $sectioninfo->OPTIONNAME
     * where $sectioninfo is instance of section_info, returned by
     * get_fast_modinfo($course)->get_section_info($sectionnum)
     * or get_fast_modinfo($course)->get_section_info_all()
     *
     * All format options for particular section are returned by calling:
     * $this->get_format_options($section);
     *
     * @param bool $foreditform
     * @return array
     */
    public function section_format_options($foreditform = false) {
        global $DB, $PAGE;

        if ($PAGE->pagetype == 'course-editsection') {
            $id = optional_param('id', null, PARAM_INT);

            $section = $DB->get_record('course_sections', array('id' => $id), '*', MUST_EXIST);
            $sectionnum = $section->section;

            if ($sectionnum == 0) {
                return array();
            }
        }
        if ($PAGE->pagetype == 'course-edit') {
            return $this->course_format_options(true);
        } else {

            return array(
                'format_location_restricted' => array(
                    'type' => PARAM_INT,
                    'label' => new lang_string('restricted', 'format_location'),
                    'element_type' => 'checkbox',
                    'cache' => true,
                    'default' => FORMAT_LOCATION_UNRESTRICTED,
                ),
                'format_location_address' => array(
                    'type' => PARAM_TEXT,
                    'label' => new lang_string('address', 'format_location'),
                    'element_type' => 'textarea',
                    'cache' => true,
                    'default' => '',
                ),
                'format_location_latitude' => array(
                    'type' => PARAM_FLOAT,
                    'label' => new lang_string('latitude', 'format_location'),
                    'element_type' => 'text',
                    'cache' => true,
                    'default' => '',
                ),
                'format_location_longitude' => array(
                    'type' => PARAM_FLOAT,
                    'label' => new lang_string('longitude', 'format_location'),
                    'element_type' => 'text',
                    'cache' => true,
                    'default' => '',
                )
            );
        }
    }

    /**
     * Adds format options elements to the course/section edit form
     *
     * This function is called from {@link course_edit_form::definition_after_data()}
     *
     * @param MoodleQuickForm $mform form the elements are added to
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form
     * @return array array of references to the added form elements
     */
    public function create_edit_form_elements(&$mform, $forsection = true) {
        global $PAGE, $CFG;

        if ($PAGE->pagetype == 'course-edit') {

            $elements = parent::create_edit_form_elements($mform, $forsection);

            // Increase the number of sections combo box values if the user has increased the number of sections
            // using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
            // reduced below the number of sections already set for the course on the site administration course
            // defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
            // activities / resources.
            if (!$forsection) {
                $maxsections = get_config('moodlecourse', 'maxsections');
                $numsections = $mform->getElementValue('numsections');
                $numsections = $numsections[0];
                if ($numsections > $maxsections) {
                    $element = $mform->getElement('numsections');
                    for ($i = $maxsections+1; $i <= $numsections; $i++) {
                        $element->addOption("$i", $i);
                    }
                }
            }
            return $elements;
        } else {
            $validationerror = optional_param('validationerror', null, PARAM_INT);
            $mform->addElement('header', 'gpssettings', new lang_string('editsection_geo_heading', 'format_location'));
            $mform->addHelpButton('gpssettings', 'gpshelp', 'format_location');
            if ($validationerror == 'yes') {
                $error = html_writer::div(new lang_string('validationerror', 'format_location'), 'bold red error');
                $errorlabel = html_writer::div(new lang_string('error'), 'bold red error');
                $mform->addElement('static', 'validationerrror', $errorlabel, $error);
                $mform->addHelpButton('validationerrror', 'errorhelp', 'format_location');
            }
            $mform->addElement('checkbox', 'format_location_restricted', new lang_string('active', 'format_location'));
            $mform->setDefault('format_gps_restricted', FORMAT_LOCATION_UNRESTRICTED);
            $attributes = array('size' => '100', 'width' => '500', 'maxlength' => '100');
            $mform->addElement('text', 'format_location_address', new lang_string('address', 'format_location'), $attributes);
            $mform->setType('format_location_address', PARAM_TEXT);
            $mform->addElement('text', 'format_location_latitude', new lang_string('latitude', 'format_location'));
            $mform->addElement('text', 'format_location_longitude', new lang_string('longitude', 'format_location'));
            $mform->addRule('format_location_address', null, 'maxlength', 255, 'client');
            $mform->addRule('format_location_longitude', null, 'numeric', null, 'client');
            $mform->addRule('format_location_longitude', null, 'numeric', null, 'client');
            $mform->addRule('format_location_latitude', null, 'numeric', null, 'client');
            $mform->setType('format_location_latitude', PARAM_RAW);
            $mform->setType('format_location_longitude', PARAM_RAW);
            $mform->disabledIf('format_location_address', 'format_location_restricted', 'notchecked');
            $mform->disabledIf('format_location_latitude', 'format_location_restricted', 'notchecked');
            $mform->disabledIf('format_location_longitude', 'format_location_restricted', 'notchecked');
        }
    }

    public function editsection_form($action, $customdata = array()) {
        global $CFG, $DB, $COURSE;

        if (!array_key_exists('course', $customdata)) {
            $customdata['course'] = $this->get_course();
        }
        $form = new editsection_form($action, $customdata);

        if ($form->is_submitted()) {
            $data = $form->get_data();

            if ($data->format_location_restricted == '1') {
                if ($data->format_location_latitude == null || $data->format_location_latitude == '' ||
                        $data->format_location_longitude == null || $data->format_location_longitude == '') {
                    $url = new moodle_url('/course/editsection.php', array(
                                'id' => $data->id,
                                'validationerror' => 'yes'));
                    redirect($url);
                }
            }
        }
        return $form;
    }

}
