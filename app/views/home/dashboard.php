<h1><?php echo $this->lang($pageTitle); ?></h1>

<hr>

<div class="row">
	<div class="col-12 col-sm-5">
		<h3><?php echo $this->lang('sales-by-month'); ?></h3>

		<canvas id="sales-by-month"></canvas>
	</div>

	<div class="col-12 col-sm-7">
		<h3><?php echo $this->lang('top-10-best-sellers'); ?></h3>

		<canvas id="best-sellers"></canvas>
	</div>

	<div class="col-12">
		<h3><?php echo $this->lang('last-sales'); ?></h3>

		<?php
			$lastSalesTable = new \PHC\Components\Table;

			$lastSalesTable->resource = $this->lastSales;
			$lastSalesTable->columns = [
				'#' => 'id',
				$this->lang('salesman') => function($order) {
					$link = '<a href="/employees/view/%d" title="%s">%s</a>';

					return sprintf(
						$link,
						$order->salesman->id,
						$order->salesman->name,
						$order->salesman->name
					);
				},
				$this->lang('customer') => function($row) {
					$link = '<a href="/customers/view/%d" title="%s">%s</a>';

					return sprintf(
						$link,
						$row->customer->id,
						$row->customer->name,
						$row->customer->name
					);
				},
				$this->lang('date') => [ 'date', [ 'method' => 'format', 'args' => [ DATE_FORMAT ] ] ],
				$this->lang('total') => function($order) {
					$total = 0;

					if (!empty($order->items)) {
						foreach ($order->items as $item) {
							$total += $item->price * $item->quantity;
						}
					}

					return '$ ' . number_format($total, 2);
				},
				$this->lang('actions') => function ($order) {
					$view = new \PHC\Components\Form\Button;

					$view->name = 'view';
					$view->type = 'link';
					$view->title = $this->lang('view');
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
		<h3><?php echo $this->lang('best-customers'); ?></h3>

		<?php
			$bestCustomersTable = new \PHC\Components\Table;

			$bestCustomersTable->resource = $this->bestCustomers;
			$bestCustomersTable->columns = [
				$this->lang('total-spent') => [ 'function' => 'number_format', 'args' => [ '{row->total}', 2, '.', '' ] ],
				$this->lang('customer') => function($row) {
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
		<h3><?php echo $this->lang('last-buy'); ?></h3>

		<?php
			$customerLastBuyTable = new \PHC\Components\Table;

			$customerLastBuyTable->resource = $this->customerLastBuy;
			$customerLastBuyTable->columns = [
				$this->lang('date') => function($row) {
					list($year, $month, $day) = explode('-', $row['date']);
					$time = mktime(0, 0, 0, $month, $day, $year);

					return date(DATE_FORMAT, $time);
				},
				$this->lang('customer') => function($row) {
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
