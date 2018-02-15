<h1><?=$pageTitle?></h1>

<hr>

<div class="row">
	<div class="col-12 col-xl-6">
		<canvas id="best-sellers"></canvas>
	</div>
</div>

<?php // vd($this->list); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
<script>
	var COLORS = [
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

	var randomScalingFactor = function() {
        return Math.round(Math.random() * 100);
    };

    var config = {
        type: 'pie',
        data: {
            datasets: [{
                data: [<?php
					foreach ($this->list as $item) {
						echo $item['total'] . ',';
					}
				?>],
                backgroundColor: [
                    COLORS[0],
                    COLORS[1],
                    COLORS[2],
                    COLORS[3],
                    COLORS[4],
                    COLORS[5],
                    COLORS[6],
                    COLORS[7],
                    COLORS[8],
                    COLORS[9],
                ],
                label: 'Dataset 1'
            }],
            labels: [<?php
				foreach ($this->list as $item) {
					echo '"' . $item['name'] . '",';
				}
			?>]
        },
        options: {
            responsive: true,
			onResize: (chart, newSize) => {
				const _old = chart.legend.display;
				window.config.options.legend.display = newSize.width >= 550;
				const _new = chart.legend.display;

				if (_old != _new) {
					window.myPie.update();
				}
			},
			legend: {
				display: document.body.clientWidth >= 580,
				position: 'right',
				fullWidth: true,
			},
			title: {
				display: true,
				text: 'Top 10 best sellers in the last 30 days'
			}
        }
    };

    window.onload = function() {
        var ctx = document.getElementById("best-sellers").getContext("2d");
        window.myPie = new Chart(ctx, config);
    };
</script>
