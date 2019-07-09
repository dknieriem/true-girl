(function($) {
	'use strict';

	/**
	 * Variables
	 */
	var $lineToggles = $(".line-toggle");
	var $range = $(document.getElementById('mc4wp-graph-range'));
	var $customRangeOptions = $(document.getElementById('mc4wp-graph-custom-range-options'));
	var previousPoint;
	var $graph = $(document.getElementById('mc4wp-graph'));
	var datasets = mc4wp_statistics_data || {};
 	var settings = mc4wp_statistics_settings || {};

	/**
	 * Functions
	 */
	function plotGraph() {
		var graphData = [];

		$lineToggles.filter(":checked").each(function() {
			graphData.push(datasets[this.value]);
		});

		$.plot(
			$graph,
			graphData,
			{
				xaxis: {
					mode: "time",
					//min: startDate.getTime(),
					//max: endDate.getTime(),
					timeFormat: "%d/%b",
					minTickSize: settings.ticksize
				},
				yaxis: {
					min: 0, tickDecimals: 0
				},
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					hoverable: true
				}

			}
		);
	}

	function showTooltip(x, y, contents) {
		$('<div class="tooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid #fdd',
			padding: '2px',
			'background-color': '#fee',
			opacity: 0.80
		}).appendTo(document.body).fadeIn(200);
	}

	function toggleCustomRangeOptions(e) {
		var show = e.target.value === 'custom';
		$customRangeOptions.toggle( show );
	}

	function plotHover(event, pos, item) {
		var xEl = document.getElementById('x');
		var yEl = document.getElementById('y');
		if(xEl) { xEl.innerText = pos.x.toFixed(2); }
		if(yEl) { yEl.innerText = pos.y.toFixed(2); }

		if (item) {
			if (previousPoint != item.dataIndex) {
				previousPoint = item.dataIndex;
				$(".tooltip").remove();

				var x = item.datapoint[0],
					y = item.datapoint[1];

				showTooltip( item.pageX, item.pageY, item.series.label + ': ' + y );
			}
		} else {
			$(".tooltip").remove();
			previousPoint = null;
		}
	}

	/**
	 * Bind event handlers$graph
	 */
	$lineToggles.change(plotGraph);
	$range.change(toggleCustomRangeOptions);
	$graph.bind("plothover", plotHover);

	plotGraph();
})(window.jQuery);
