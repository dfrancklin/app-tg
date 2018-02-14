<h1><?=$pageTitle?></h1>

<hr>

<dl class="row">
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">ID:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->id; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Customer: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<a href="/customers/view/<?php echo $this->order->customer->id; ?>" title="<?php echo $this->order->customer->name; ?>">
			<?php echo $this->order->customer->name; ?>
		</a>
	</dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4">Salesman: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<a href="/employees/view/<?php echo $this->order->salesman->id; ?>" title="<?php echo $this->order->salesman->name; ?>">
			<?php echo $this->order->salesman->name; ?>
		</a>
	</dd>
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
				return '<a href="/products/view/' . $product->id . '">' . $product->name . '</a>';
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
		$voltar->additional = ['onclick' => '(function(e) { e.preventDefault(); window.history.back(); })(event)'];

		$voltar->render();
	?>
</div>
