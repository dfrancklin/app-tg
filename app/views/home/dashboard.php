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

	<div class="col-12">
		<h3>Last Sales</h3>

		<?php
			$lastSalesTable = new \PHC\Components\Table;

			$lastSalesTable->resource = $this->lastSales;
			$lastSalesTable->columns = [
				'#' => 'id',
				'Salesman' => function($order) {
					$link = '<a href="/employees/view/%d" title="%s">%s</a>';

					return sprintf(
						$link,
						$order->salesman->id,
						$order->salesman->name,
						$order->salesman->name
					);
				},
				'Customer' => function($row) {
					$link = '<a href="/customers/view/%d" title="%s">%s</a>';

					return sprintf(
						$link,
						$row->customer->id,
						$row->customer->name,
						$row->customer->name
					);
				},
				'Date' => [
					'date',
					[
						'method' => 'format',
						'args' => ['m/d/Y']
					]
				],
				'Total' => function($order) {
					$total = 0;

					if (!empty($order->items)) {
						foreach ($order->items as $item) {
							$total += $item->price * $item->quantity;
						}
					}

					return '$ ' . number_format($total, 2);
				},
				'Actions' => function ($order) {
					$view = new \PHC\Components\Form\Button;

					$view->name = 'View';
					$view->type = 'link';
					$view->icon = 'visibility';
					$view->size = 's';
					$view->style = 'primary';
					$view->action = '/orders/view/' . $order->id;

					return $view;
				}
			];

			$lastSalesTable->render();
		?>
	</div>

	<div class="col-12 col-sm-6">
		<h3>Best Customers</h3>

		<?php
			$bestCustomersTable = new \PHC\Components\Table;

			$bestCustomersTable->resource = $this->bestCustomers;
			$bestCustomersTable->columns = [
				'Total spent' => [
					'function' => 'number_format',
					'args' => ['{row->total}', 2, '.', '']
				],
				'Customer' => function($row) {
					$link = '<a href="/customers/view/%d" title="%s">%s</a>';

					return sprintf(
						$link,
						$row->customer->id,
						$row->customer->name,
						$row->customer->name
					);
				}
			];

			$bestCustomersTable->render();
		?>
	</div>

	<div class="col-12 col-sm-6">
		<h3>Customer's Last Buy</h3>

		<?php
			$customerLastBuyTable = new \PHC\Components\Table;

			$customerLastBuyTable->resource = $this->customerLastBuy;
			$customerLastBuyTable->columns = [
				'Date' => function($row) {
					list($year, $month, $day) = explode('-', $row['date']);
					$time = mktime(0, 0, 0, $month, $day, $year);

					return date('m/d/Y', $time);
				},
				'Customer' => function($row) {
					$link = '<a href="/customers/view/%d" title="%s">%s</a>';

					return sprintf(
						$link,
						$row['customer_id'],
						$row['name'],
						$row['name']
					);
				}
			];

			$customerLastBuyTable->render();
		?>
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
