<h1><?php echo $this->lang($pageTitle); ?></h1>

<hr>

<dl class="row">
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('id'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->employee->id; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('name'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->employee->name; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('email'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->employee->email; ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('admission-date'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8"><?php echo $this->employee->admissionDate->format(DATE_FORMAT); ?></dd>
	<dt class="col-lg-2 col-md-2 col-sm-3 col-4"><?php echo $this->lang('roles'); ?>:</dt>
	<dd class="col-lg-10 col-md-10 col-sm-9 col-8">
		<?php
			echo (function($roles) {
				$names = array_map(function($item) {
					return $item->name;
				}, $roles);

				return implode(', ', $names);
			})($this->employee->roles);
		?>
	</dd>
</dl>

<?php
	$roles = array_map(function($role) {
		return $role->name;
	}, $this->employee->roles);

	if (
		(
			in_array('ADMIN', $roles) ||
			in_array('SALES', $roles)
		) &&
		!empty($this->employee->orders)
	) {
		$security = $this->security;
		$table = new \PHC\Components\Table;
		$table->resource = $this->employee->orders;
		$table->columns = [
			'#' => 'id',
			$this->lang('customer') => function($order) {
				$link = '<a href="/customers/view/%d" title="%s">%s</a>';

				return sprintf(
					$link,
					$order->customer->id,
					$order->customer->name,
					$order->customer->name
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
		];
		$table->actionsLabel = $this->lang('actions');
		$table->actions = [
			function ($order) {
				if (!$order->finished) {
					return;
				}

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
		$table->render();
	}
?>

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
