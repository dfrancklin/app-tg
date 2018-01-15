<h1><?=$pageTitle?></h1>

<hr>

<dl class="row">
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">ID:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->id; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Customer: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->customer->name; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Salesman: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->salesman->name; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Date: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->date->format('d/m/Y'); ?></dd>
</dl>

<?php
	$table = new \PHC\Components\Table;
	$table->resource = $this->order->items;
	$table->columns = [
		'#' => ['product', 'name'],
		'Quantity' => 'quantity',
		'Price' => function($item) {
			return '$ ' . number_format($item->price, 2);
		},
		'SubTotal' => function($item) {
			return '$ ' . number_format($item->quantity * $item->price, 2);
		},
	];
	$table->render();
?>

<p class="text-right">
	<strong>Total: </strong>
	<?php
		echo (function($order) {
			$total = 0;

			if (!empty($order->items)) {
				foreach ($order->items as $item) {
					$total += $item->quantity * $item->price;
				}
			}

			return '$ ' . number_format($total, 2);
		})($this->order);
	?>
</p>

<div class="text-right">
	<?php
		$voltar = new \PHC\Components\Form\Button;
		$voltar->name = 'voltar';
		$voltar->type = 'link';
		$voltar->icon = 'arrow_back';
		$voltar->action = '/orders';
		$voltar->render();
	?>
</div>
