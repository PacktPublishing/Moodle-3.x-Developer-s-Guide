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
 * @package    filter
 * @subpackage swedishchef
 * @copyright  Ian Wild <ian.wild@heavyhorse.co.uk>
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_swedishchef extends moodle_text_filter {
    function filter($text, array $options = array()) {
        global $CFG;

        if (empty($text) or is_numeric($text)) {
            return $text;
        }

        $patterns = array (
                "/a\B/",
                "/an/",
                "/au/",
                "/en\b/",
                "/\Bew/",
                "/\Bf/",
                "/\Bi/",
                "/\Bir/",
                "/\bo/",
                "/ow/",
                "/ph/",
                "/th\b/",
                "/\Btion/",
                "/\Bu/",
                "/\bU/",
                "/y\b/",
                "/v/",
                "/w/",
                "/ooo/");
        
        $replacements = array (
                "e",
                "un",
                "oo",
                "ee",
                "oo",
                "ff",
                "ee",
                "ur",
                "oo",
                "oo",
                "f",
                "t",
                "shun",
                "oo",
                "Oo",
                "ai",
                "f",
                "v",
                "oo");
        
        
        $text = preg_replace($patterns, $replacements, $text );
        
        /*
        $text = preg_replace( "/a\B/", "e", $text );
        $text = preg_replace( "/an/", "un", $text );
        $text = preg_replace( "/au/", "oo", $text );
        $text = preg_replace( "/en\b/", "ee", $text );
        $text = preg_replace( "/\Bew/", "oo", $text );
        $text = preg_replace( "/\Bf/", "ff", $text );
        $text = preg_replace( "/\Bi/", "ee", $text );
        $text = preg_replace( "/\Bir/", "ur", $text );
        $text = preg_replace( "/\bo/", "oo", $text );
        $text = preg_replace( "/ow/", "oo", $text );
        $text = preg_replace( "/ph/", "f", $text );
        $text = preg_replace( "/th\b/", "t", $text );
        $text = preg_replace( "/\Btion/", "shun", $text );
        $text = preg_replace( "/\Bu/", "oo", $text );
        $text = preg_replace( "/\bU/", "Oo", $text );
        $text = preg_replace( "/y\b/", "ai", $text );
        $text = preg_replace( "/v/", "f", $text );
        $text = preg_replace( "/w/", "v", $text );
        $text = preg_replace( "/ooo/", "oo", $text );
        */
        
        if(strlen($text) > 20) {
            $text .= " Børk! Børk! Børk!";
        }

        return $text;
    }
    
    /*
     * Add the javascript to enable swedish chef language processing on this page.
    *
    * @param moodle_page $page The current page.
    * @param context $context The current context.
    */
    public function setup($page, $context) {
        // This only requires execution once per request.
        static $jsinitialised = false;
    
        if (empty($jsinitialised)) {
            $page->requires->yui_module('moodle-filter_swedishchef-loader', 'M.filter_swedishchefloader.configure');
    
            $jsinitialised = true;
        }
    }
}