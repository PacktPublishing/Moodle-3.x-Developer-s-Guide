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
 * @package     local_duallang
 * @copyright   2016 Ian Wild - based on local_stringman by David Mudrak <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_duallang;

defined('MOODLE_INTERNAL') || die();

/**
 * Override the get_string() function to display two language strings, in this instance UK English and simplified Chinese Mandarin.
 *
 * Based on local_stringman by David Mudrak <david@moodle.com> 
 * @copyright 2015 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class duallang_string_manager extends \core_string_manager_standard {

    /**
     * Implementation of the get_string() method to display both simplified Chinese and UK English simultaneously.
     *
     * @param string $identifier the identifier of the string to search for
     * @param string $component the component the string is provided by
     * @param string|object|array $a optional data placeholder
     * @param string $lang moodle translation language, null means use current
     * @return string
     */
    public function get_string($identifier, $component = '', $a = null, $lang = null) {
        $string = ''; // returns an empty string by default
        
        if(get_config('local_duallang', 'enabled')) {
            
            $order = get_config('local_duallang', 'readingorder');
            
            $primarylang = parent::get_string($identifier, $component, $a, get_config('local_duallang', 'primarylanguage'));
        
            $secondarylang = parent::get_string($identifier, $component, $a, get_config('local_duallang', 'secondarylanguage'));
            
            // ordering is only left to right or right to left so just check the first letter of the string 
            if($order[0] == 'L') {
                $string = $primarylang . ' (' . $secondarylang . ')';
            } else {
                $string = '(' . $secondarylang . ') ' . $primarylang;
            }
        } else {
            $string = parent::get_string($identifier, $component, $a, $lang);
        }
        return $string;
    }
}
