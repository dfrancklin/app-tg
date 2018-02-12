<h1><?=$pageTitle?></h1>

<hr>

<?php vd($_SERVER); ?>

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

<div>
	<?php
		$table = new \PHC\Components\Table;
		$table->resource = $this->order->items;
		$table->columns = [
			'#' => ['product', 'id'],
			'Picture' => function($row) {
				if ($row->product->picture) {
					return sprintf(
						'<img src="%s" title="%s" alt="%s" class="img-fluid rounded d-block mx-auto">',
						$row->product->picture,
						$row->product->name,
						$row->product->name
					);
				}
			},
			'Name' => function($item) {
				$product = $item->product;
				return '<a href="/products/form/' . $product->id . '">' . $product->name . '</a>';
			},
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
</div>

<div class="text-right">
	<?php
		$voltar = new \PHC\Components\Form\Button;
		$voltar->name = 'voltar';
		$voltar->type = 'link';
		$voltar->icon = 'arrow_back';

		$ref = $_SERVER['HTTP_REFERER'] ?? '';
		$uri = $_SERVER['REQUEST_URI'];
		$server = $_SERVER['SERVER_NAME'];

		if (strpos($ref, $uri) || !strpos($ref, $server)) {
			$voltar->action = '/orders';
		} else {
			$voltar->additional = ['onclick' => '(function(e) { e.preventDefault(); window.history.back(); })(event)'];
		}

		$voltar->render();
	?>
</div>
