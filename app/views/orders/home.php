<h1>
	<?=$pageTitle?>

	<a href="/orders/form" class="btn btn-primary">
		New <span class="material-icons">add_circle_outline</span>
	</a>
</h1>

<hr>

<?php
	$table = new \PHC\Components\Table;
	$table->resource = $this->orders;
	$table->columns = [
		'#' => 'id',
		'Customer' => ['customer', 'name'],
		'Salesman' => ['salesman', 'name'],
		'Date' => ['date', ['method' => 'format', 'args' => ['d/m/Y']]],
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
			$view->action = '/orders/view/{row->id}';

			return $view;
		},
		function ($order) {
			if ($order->finished) {
				return;
			}

			$edit = new \PHC\Components\Form\Button;

			$edit->name = 'Edit';
			$edit->type = 'link';
			$edit->icon = 'edit';
			$edit->size = 's';
			$edit->style = 'success';
			$edit->action = '/orders/form/{row->id}';

			return $edit;
		},
		function ($order) {
			$delete = new \PHC\Components\Form\Button;

			$delete->name = 'Delete';
			$delete->icon = 'delete';
			$delete->size = 's';
			$delete->style = 'danger';
			$delete->additional = [
				'data-toggle'=> 'modal',
				'data-target'=> '#confirm-modal',
				'data-id'=> '{row->id}'
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
	$modal->title = 'Are you sure?';
	$modal->body = '<p>Are you sure that you want to delete this item permanently?</p>';
	$modal->actions = [
		(function () {
			$delete = new \PHC\Components\Form\Button;

			$delete->name = 'Delete';
			$delete->type = 'link';
			$delete->icon = 'delete';
			$delete->style = 'danger';
			$delete->additional = ['data-destiny' => '/orders/delete/'];

			return $delete;
		})()
	];
	$modal->render();
?>
