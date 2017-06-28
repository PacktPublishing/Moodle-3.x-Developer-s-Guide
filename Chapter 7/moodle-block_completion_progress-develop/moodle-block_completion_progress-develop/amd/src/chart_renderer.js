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
 * Encapsules the behavior for creating a progress chart in Moodle.
 *
 * Manages the UI.
 *
 * @module     block_completion_progress/chart_renderer
 * @class      chart_renderer
 * @package    block_completion_progress
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.1
 */

define(['jquery'], function($) {  
	
	var links = [];
	
	var t = {
		
        drawChart: function(chartEl, chartType, dataSet, showText) {
       
			var completion_data = $.parseJSON(dataSet);
			var array_len = completion_data.activities.length;
			
			var labels = [];
			var data = [];
			var background = [];
			
			// only handle complete and incomplete at the moment
			var col_complete = completion_data.colors.completed_colour;
			var col_incomplete = completion_data.colors.notCompleted_colour;
			var col_submitted = completion_data.colors.submittednotcomplete_colour;
			var col_failed = completion_data.colors.futureNotCompleted_colour;
			
			for (var i = 0; i < array_len; i++) {
				labels.push(completion_data.activities[i].name);
				// Note that the pie chart segments will all be the same size
				data.push('1'); 
				links.push(completion_data.activities[i].link);
				var status = completion_data.activities[i].status;
				switch (status) {
					case 'complete':
						background.push(col_complete);
						break;
					case 'passed':
						background.push(col_complete);
						break;
					case 'failed':
						background.push(col_failed);
						break;
					case 'submitted':
						background.push(col_submitted);
						break;
					default:
						background.push(col_incomplete);
						break;
				}
			}
			
			Chart.pluginService.register({
				beforeDraw: function (chart) {
					if (chart.config.options.elements.center) {
				        //Get ctx from string
				        var ctx = chart.chart.ctx;
				        
								//Get options from the center object in options
				        var centerConfig = chart.config.options.elements.center;
				      	var fontStyle = centerConfig.fontStyle || 'Arial';
								var txt = centerConfig.text;
				        var color = centerConfig.color || '#000';
				        var sidePadding = centerConfig.sidePadding || 20;
				        var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
				        //Start with a base font of 30px
				        ctx.font = "30px " + fontStyle;
				        
								//Get the width of the string and also the width of the element minus 10 to give it 5px side padding
				        var stringWidth = ctx.measureText(txt).width;
				        var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;
		
				        // Find out how much the font can grow in width.
				        var widthRatio = elementWidth / stringWidth;
				        var newFontSize = Math.floor(30 * widthRatio);
				        var elementHeight = (chart.innerRadius * 2);
		
				        // Pick a new font size so it will not be larger than the height of label.
				        var fontSizeToUse = Math.min(newFontSize, elementHeight);
		
						//Set font settings to draw it correctly.
				        ctx.textAlign = 'center';
				        ctx.textBaseline = 'middle';
				        var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
				        var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
				        ctx.font = fontSizeToUse+"px " + fontStyle;
				        ctx.fillStyle = color;
				        
				        //Draw text in center
				        ctx.fillText(txt, centerX, centerY);
					}
				}
			});
			
			var chartData = {
				labels: labels,
				datasets:[
					{
						label: "Completions",
						data: data,
						backgroundColor: background
				    }
				]
			};

    	    // Get the context of the canvas element we want to select
    	    var ctx = document.getElementById(chartEl).getContext("2d");

    	    var centerTxt = ''; 
    	    if(showText == true) {
    	    	centerTxt = completion_data.percentage + '%';
    	    }
    	    
    	    // Instantiate a new chart
    	    var myChart = new Chart(ctx, {
				type: chartType,
				data: chartData,
				options: {
					elements: {
						center: {
							text: centerTxt,
							color: '#000000', // Default is #000000
							fontStyle: 'Arial', // Default is Arial
							sidePadding: 40 // Default is 20 (as a percentage)
						}
					},
					legend: {
						display: false
					},
				    tooltips: {
			            callbacks: {
			                label: function(tooltipItem, data) {
			                	String.prototype.trunc = 
			                	      function(n){
			                	          return this.substr(0,n-1)+(this.length>n?'...':'');
			                	      };
			                	      
			                    var allData = data.datasets[tooltipItem.datasetIndex].data;
			                    var tooltipLabel = data.labels[tooltipItem.index];
			                    // truncate the label to 15 characters plus an ellipsis if necessary:
			                    return tooltipLabel.trunc(15);
			                }
			            }
			        }
				}
			});
    	    
    	    $(document).ready(
    		  function () {
    		    var canvas = document.getElementById(chartEl);
    		    
    		    canvas.onclick = function (evt) {
    		      var activePoints = myChart.getElementsAtEvent(evt);
    		      var chartData = activePoints[0]['_chart'].config.data;
    		      var idx = activePoints[0]['_index'];

    		      var url = links[idx];
    		      window.location.href = url;
    		      return false;
    		    };
    		});
        },

        drawBar: function(chartEl, dataSet) {
       
			var completion_data = $.parseJSON(dataSet);
			var array_len = completion_data.activities.length;
			
			var labels = [];
			var data = [];
			var background = [];
			
			// only handle complete and incomplete at the moment
			var col_complete = completion_data.colors.completed_colour;
			var col_incomplete = completion_data.colors.notCompleted_colour;
			var col_submitted = completion_data.colors.submittednotcomplete_colour;
			var col_failed = completion_data.colors.futureNotCompleted_colour;
			
			for (var i = 0; i < array_len; i++) {
				labels.push(completion_data.activities[i].name);
				// Note that the pie chart segments will all be the same size
				data.push('1'); 
				links.push(completion_data.activities[i].link);
				var status = completion_data.activities[i].status;
				switch (status) {
					case 'complete':
						background.push(col_complete);
						break;
					case 'passed':
						background.push(col_complete);
						break;
					case 'failed':
						background.push(col_failed);
						break;
					case 'submitted':
						background.push(col_submitted);
						break;
					default:
						background.push(col_incomplete);
						break;
				}
			}
			
			var chartData = {
				labels: labels,
				datasets:[
					{
						label: "Completions",
						data: data,
						backgroundColor: background
				    }
				]
			};

    	    // Get the context of the canvas element we want to select
    	    var ctx = document.getElementById(chartEl).getContext("2d");
    	    
    	    Chart.defaults.global.tooltips.enabled = false;
    	    
    	    
    	    // Instantiate a new chart
    	    var myChart = new Chart(ctx, {
				type: 'horizontalBar',
				data: chartData,	
				options: {
					scales: {
				        xAxes: [{
				            ticks: {
				                fontFamily: "'Open Sans Bold', sans-serif",
				                fontSize:11,
				                display: false,
				            },
				            scaleLabel:{
				                display:false
				            },
				            gridLines: {
				            	display:false
				            }, 
				            stacked: true
				        }],
				        yAxes: [{
				            gridLines: {
				                display:false,
				                color: "#fff",
				                zeroLineColor: "#fff",
				                zeroLineWidth: 0
				            },
				            ticks: {
				                fontFamily: "'Open Sans Bold', sans-serif",
				                fontSize:11
				            },
				            stacked: true
				        }]
				    },
				    legend:{
				        display:false
				    },
				    
				}
			});
    	    
    	    $(document).ready(
    		  function () {
    		    var canvas = document.getElementById(chartEl);
    		    
    		    canvas.onclick = function (evt) {
    		      var activePoints = myChart.getElementsAtEvent(evt);
    		      var chartData = activePoints[0]['_chart'].config.data;
    		      var idx = activePoints[0]['_index'];

    		      var url = links[idx];
    		      window.location.href = url;
    		      return false;
    		    };
    		});
        },
	};
	
	return t;
});

