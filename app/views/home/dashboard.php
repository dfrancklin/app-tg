<h1><?=$pageTitle?></h1>

<hr>

<div class="row">
	<div class="col-12 col-sm-5">
		<h3>Sales by Month</h3>
		<canvas id="sales-by-month"></canvas>
	</div>
	<div class="col-12 col-sm-7">
		<h3>Top 10 best sellers in the last 30 days</h3>
		<canvas id="best-sellers"></canvas>
	</div>
	<div class="col-12 col-sm-4">
		<h3>Last Sales</h3>
		<?php vd($this->lastSales) ?>
	</div>
	<div class="col-12 col-sm-4">
		<h3>Best Customers</h3>
		<?php vd($this->bestCustomers) ?>
	</div>
	<div class="col-12 col-sm-4">
		<h3>Customers That Needs Attention</h3>
		<?php vd($this->customersThatNeedsAttention) ?>
	</div>
</div>

<script src="/public/js/chart.min.js"></script>
<script src="/public/js/dashboard.js"></script>
<script>
	window.onload = () => {
		pieChart(
			[<?php
				foreach ($this->bestSellers as $item) {
					echo $item['total'] . ',';
				}
			?>],
			[<?php
				foreach ($this->bestSellers as $item) {
					echo '"' . $item['name'] . '",';
				}
			?>]
		);

		barChart(
			[<?php
				foreach ($this->salesByMonth as $item) {
					echo number_format($item->total, 2, '.', '') . ',';
				}
			?>],
			[<?php
				foreach ($this->salesByMonth as $item) {
					echo '"' . $item->month . '",';
				}
			?>]
		);
	};
</script>
