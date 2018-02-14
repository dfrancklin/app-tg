<h1><?=$pageTitle?></h1>

<hr>

<dl class="row">
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">ID:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->customer->id; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Name:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->customer->name; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">E-mail:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->customer->email; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Phone:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->customer->phone; ?></dd>
</dl>

<?php
	$security = $this->security;
	$table = new \PHC\Components\Table;
	$table->resource = $this->customer->orders;
	$table->columns = [
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
		'Date' => [ 'date', [ 'method' => 'format', 'args' => [ 'd/m/Y' ] ] ],
		'Total' => function($order) {
			$total = 0;

			if (!empty($order->items)) {
				foreach ($order->items as $item) {
					$total += $item->price * $item->quantity;
				}
			}

			return '$ ' . number_format($total, 2);
		},
	];
	$table->actions = [
		function ($order) {
			if (!$order->finished) {
				return;
			}

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
	$table->render();
?>

<div class="text-right">
	<?php
		$voltar = new \PHC\Components\Form\Button;

		$voltar->name = 'voltar';
		$voltar->type = 'link';
		$voltar->icon = 'arrow_back';
		$voltar->additional = ['onclick' => '(function(e) { e.preventDefault(); window.history.back(); })(event)'];

		$voltar->render();
	?>
</div>
