<h1>
	<?php echo $this->lang($pageTitle); ?>

	<a href="/orders/form" class="btn btn-primary">
		<?php echo $this->lang('new'); ?>
		<span class="material-icons">add_circle_outline</span>
	</a>
</h1>

<hr>

<?php
	$security = $this->security;
	$table = new \PHC\Components\Table;
	$table->resource = $this->orders;
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
		$this->lang('salesman') => function($order) {
			$link = '<a href="/employees/view/%d" title="%s">%s</a>';

			return sprintf(
				$link,
				$order->salesman->id,
				$order->salesman->name,
				$order->salesman->name
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
		},
		function ($order) {
			if ($order->finished) {
				return;
			}

			$edit = new \PHC\Components\Form\Button;

			$edit->name = 'edit';
			$edit->type = 'link';
			$edit->title = $this->lang('edit');
			$edit->icon = 'edit';
			$edit->size = 's';
			$edit->style = 'success';
			$edit->action = '/orders/form/' . $order->id;

			return $edit;
		},
		function ($order) use ($security) {
			if ($order->finished && !$security->hasRoles(['ADMIN'])) {
				return;
			}

			$delete = new \PHC\Components\Form\Button;

			$delete->name = 'delete';
			$delete->icon = 'delete';
			$delete->title = $this->lang('delete');
			$delete->size = 's';
			$delete->style = 'danger';
			$delete->additional = [
				'data-toggle'=> 'modal',
				'data-target'=> '#confirm-modal',
				'data-id'=> $order->id
			];

			return $delete;
		}
	];
	$table->render();

	$pagination = new \PHC\Components\Pagination;
	$pagination->route = $this->router->getActiveRoute();
	$pagination->active = $this->page;
	$pagination->total = $this->totalPages;
	$pagination->render();

	$modal = new \PHC\Components\Modal;
	$modal->name = 'confirm-modal';
	$modal->title = $this->lang('confirm-modal-title');
	$modal->body = $this->lang('confirm-modal-message');
	$modal->closeButtonLabel = $this->lang('close');
	$modal->actions = [
		(function () {
			$delete = new \PHC\Components\Form\Button;

			$delete->name = 'delete';
			$delete->type = 'link';
			$delete->title = $this->lang('delete');
			$delete->icon = 'delete';
			$delete->style = 'danger';
			$delete->additional = ['data-destiny' => '/orders/delete/'];

			return $delete;
		})()
	];
	$modal->render();
?>
