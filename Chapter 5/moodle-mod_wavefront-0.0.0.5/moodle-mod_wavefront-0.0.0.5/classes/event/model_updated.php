<?php
// This file is part of Moodle - http://moodle.org/
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
 * The mod_wavefront entry updated event.
 *
 * @package    mod_wavefront
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_wavefront\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_wavefront model updated event.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string concept: (optional) the concept of updated entry (after update).
 * }
 *
 * @package    mod_wavefront
 * @since      Moodle 2.7
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_updated extends \core\event\base {
    /**
     * Init method
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wavefront_model';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventmodelupdated', 'mod_wavefront');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has updated the model entry with id '$this->objectid' in " .
            "the wavefront activity with course module id '$this->contextinstanceid'.";
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url("/mod/wavefront/view.php",
                array('id' => $this->contextinstanceid,
                    'mode' => 'entry',
                    'hook' => $this->objectid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    public function get_legacy_logdata() {
        return array($this->courseid, 'glossary', 'update entry',
            "view.php?id={$this->contextinstanceid}&amp;mode=entry&amp;hook={$this->objectid}",
            $this->objectid, $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        // Make sure this class is never used without proper object details.
        if (!$this->contextlevel === CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wavefront_model', 'restore' => 'wavefront_model');
    }

    public static function get_other_mapping() {
        // Nothing to map.
        return false;
    }
}

