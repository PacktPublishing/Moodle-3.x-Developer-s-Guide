YUI.add('moodle-filter_swedishchef-loader', function (Y, NAME) {

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
 * Swedish Chef JS Loader.
 *
 * @package    filter_swedishchefloader
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
M.filter_swedishchefloader = M.filter_swedishchefloader || {

    
    /**
     * Boolean used to prevent configuring Swedish Chef twice.
     * @property _configured
     * @type Boolean
     * @default ''
     * @private
     */
    _configured: false,

    /**
     * Called by the filter when it is active on any page.
     * Subscribes to the filter-content-updated event so MathJax can respond to content loaded by Ajax.
     *
     * @method typeset
     * @param {Object} params List of optional configuration params.
     */
    configure: function(params) {
        // Listen for events triggered when new text is added to a page that needs
        // processing by a filter.
        Y.on(M.core.event.FILTER_CONTENT_UPDATED, this.contentUpdated, this);
    },

    /**
     * Handle content updated events - typeset the new content.
     * @method contentUpdated
     * @param Y.Event - Custom event with "nodes" indicating the root of the updated nodes.
     */
    contentUpdated: function(event) {
        var self = this;
       
        event.nodes.each(function (node) {
           // Do something with the node
        });
    }

};


}, '@VERSION@', {"requires": ["moodle-core-event"]});
