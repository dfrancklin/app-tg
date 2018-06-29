<h1><?php echo $this->lang($pageTitle); ?></h1>

<hr>

<dl class="row">
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('id'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->id; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('customer'); ?>: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<a href="/customers/view/<?php echo $this->order->customer->id; ?>" title="<?php echo $this->order->customer->name; ?>">
			<?php echo $this->order->customer->name; ?>
		</a>
	</dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('salesman'); ?>: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<a href="/employees/view/<?php echo $this->order->salesman->id; ?>" title="<?php echo $this->order->salesman->name; ?>">
			<?php echo $this->order->salesman->name; ?>
		</a>
	</dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('date'); ?>: </dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->order->date->format(DATE_FORMAT); ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('finished'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<?php
			if ($this->order->finished) {
				echo 'Yes';
			} else {
				echo 'No ';

				if ($this->canEdit) {
					$edit = new \PHC\Components\Form\Button;

					$edit->name = 'edit';
					$edit->type = 'link';
					$edit->title = $this->lang('edit');
					$edit->icon = 'edit';
					$edit->size = 's';
					$edit->style = 'success';
					$edit->action = '/orders/form/' . $order->id;

					$edit->render();
				}
			}
		?>
	</dd>
</dl>

<div>
	<?php
		$table = new \PHC\Components\Table;
		$table->resource = $this->order->items;
		$table->columns = [
			'#' => ['product', 'id'],
			$this->lang('picture') => function($row) {
				if ($row->product->picture) {
					return sprintf(
						'<img src="%s" title="%s" alt="%s" class="img-fluid rounded d-block mx-auto">',
						$row->product->picture,
						$row->product->name,
						$row->product->name
					);
				}
			},
			$this->lang('name') => function($item) {
				$product = $item->product;
				return '<a href="/products/view/' . $product->id . '">' . $product->name . '</a>';
			},
			$this->lang('quantity') => 'quantity',
			$this->lang('price') => function($item) {
				return '$ ' . number_format($item->price, 2);
			},
			$this->lang('subtotal') => function($item) {
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
		$back = new \PHC\Components\Form\Button;

		$back->name = 'back';
		$back->title = $this->lang('back');
		$back->type = 'link';
		$back->icon = 'arrow_back';
		$back->additional = ['onclick' => '(function(e) { e.preventDefault(); window.history.back(); })(event)'];

		$back->render();
	?>
</div>
