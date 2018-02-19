const HEX = [
	'#36a2eb', // light blue
	'#f67019', // dark orange
	'#ec1d1d', // red
	'#537bc4', // blue
	'#14c523', // green
	'#166a8f', // dark blue
	'#00a950', // dark green
	'#ffbf27', // yellow
	'#8549ba', // purple
	'#663399', // dark purple
];

const RGBA = [
	'rgba(54, 162, 235, 0.6)', // light blue
	'rgba(246, 112, 25, 0.6)', // dark orange
	'rgba(236, 29, 29, 0.6)', // red
	'rgba(83, 123, 196, 0.6)', // blue
	'rgba(20, 197, 35, 0.6)', // green
	'rgba(22, 106, 143, 0.6)', // dark blue
	'rgba(0, 169, 80, 0.6)', // dark green
	'rgba(255, 191, 39, 0.6)', // yellow
	'rgba(133, 73, 186, 0.6)', // purple
	'rgba(102, 51, 153, 0.6)', // dark purple
];

const pieChart = (data, labels) => {
	var config = {
		type: 'polarArea',
		data: {
			datasets: [{
				data: data,
				backgroundColor: RGBA,
				label: 'Best Sellers'
			}],
			labels: labels
		},
		options: {
			responsive: true,
			legend: {
				display: document.body.clientWidth >= 550,
				position: 'right',
			},
			title: {
				// display: true,
				text: 'Top 10 best sellers in the last 30 days'
			},
			scale: {
				ticks: {
					minor: {
						beginAtZero: true
					}
				},
				reverse: false
			},
			animation: {
				animateRotate: false,
				animateScale: true
			},
			onResize: (chart, newSize) => {
				const _old = chart.legend.display;
				config.options.legend.display = newSize.width >= 520;
				const _new = chart.legend.display;

				if (_old != _new) {
					pie.update();
				}
			},
		},
	};

	var ctx = document.getElementById("best-sellers").getContext("2d");
	const pie = new Chart(ctx, config);
}

const barChart = (data, labels) => {
	var config = {
		type: 'bar',
		data: {
			datasets: [{
				label: 'Sales By Month',
				data: data,
				backgroundColor: HEX[3],
				pointRadius: 0,
				fill: false,
				lineTension: 0,
				borderWidth: 2,
			}],
			labels: labels,
		},
		options: {
			responsive: true,
			title: {
				// display: true,
				text: 'Sales by Month'
			},
			legend: {
				display: false,
			},
			scales: {
				xAxes: [{
					distribution: 'series',
					ticks: {
						source: 'labels'
					}
				}],
				yAxes: [{
					scaleLabel: {
						display: true,
						labelString: 'Sales ($)'
					}
				}]
			}
		}
	};

	var ctx = document.getElementById("sales-by-month").getContext("2d");
	ctx.canvas.height = 214;
	const bar = new Chart(ctx, config);
};
