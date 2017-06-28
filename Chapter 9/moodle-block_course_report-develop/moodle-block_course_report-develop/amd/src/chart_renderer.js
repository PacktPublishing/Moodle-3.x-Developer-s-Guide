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
 * Encapsules the behavior for creating a course interaction bubble chart in Moodle.
 *
 * Manages the UI.
 *
 * @module     block_course_report/chart_renderer
 * @class      chart_renderer
 * @package    block_course_report
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.1
 */

define(['jquery'], function($) {  
	
	var t = {
		
        drawChart: function(chartEl, dataset) {
       
         /*   
           	Dummy data
           
           		dataset = {
                    "children": [{
                        "activity": "Activity 1",
                        "interactions": 2
                    }, {
                        "activity": "Activity 2",
                        "interactions": 2
                    }, {
                        "activity": "Activity 3",
                        "interactions": 1
                    }, {
                        "activity": "Activity 4",
                        "interactions": 2
                    }, {
                        "activity": "Activity 5",
                        "interactions": 3
                    }, {
                        "activity": "Activity 6",
                        "interactions": 1
                    }]
                };
*/
        		var dataset = $.parseJSON(dataset);
                
        		var diameter = 600;
                var color = d3.scaleOrdinal(d3.schemeCategory20);

                var bubble = d3.pack(dataset)
                        .size([diameter, diameter])
                        .padding(1.5);
                var svg = d3.select("#graph")
                        .append("svg")
                        .attr("width", diameter)
                        .attr("height", diameter)
                        .attr("class", "bubble");

                var nodes = d3.hierarchy(dataset)
                        .sum(function(d) { return d.interactions; });

                var node = svg.selectAll(".node")
                        .data(bubble(nodes).descendants())
                        .enter()
                        .filter(function(d){
                            return  !d.children
                        })
                        .append("g")
                        .attr("class", "node")
                        .attr("transform", function(d) {
                            return "translate(" + d.x + "," + d.y + ")";
                        });

                node.append("title")
                        .text(function(d) {
                            return d.data.activity + ' : ' + d.data.interactions + ' interaction/s';
                        });

                node.append("circle")
                        .attr("r", function(d) {
                            return d.r;
                        })
                        .style("fill", function(d) {
                            return color(d.activity);
                        });

                node.append("text")
                        .attr("dy", ".3em")
                        .style("text-anchor", "middle")
                        .text(function(d) {
                            return d.data.activity.substring(0, d.r/3);
                        });

                d3.select(self.frameElement)
                        .style("height", diameter + "px");
        }
	};
	
	return t;
});

