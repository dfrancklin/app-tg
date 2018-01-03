<h1>
	<?=$pageTitle?>

	<a href="/customers/form" class="btn btn-primary">
		New <span class="material-icons">add_circle</span>
	</a>
</h1>

<hr>

<?php
	$table = new \PHC\Components\Table;
	$table->resource = $this->customers;
	$table->columns = [
		'#' => 'id',
		'E-mail' => 'email',
		'Phone' => 'phone',
	];
	$table->actions = [
		(function () {
			$edit = new \PHC\Components\Form\Button;

			$edit->name = 'Edit';
			$edit->type = 'link';
			$edit->icon = 'edit';
			$edit->size = 's';
			$edit->style = 'success';
			$edit->action = '/customers/form/{row->id}';

			return $edit;
		})(),
		(function () {
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
		})()
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
			$delete->additional = ['data-destiny' => '/categories/delete/'];

			return $delete;
		})()
	];
	$modal->render();
?>
