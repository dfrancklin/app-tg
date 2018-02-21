<h1>
	<?=$pageTitle?>

	<a href="/employees/form" class="btn btn-primary">
		New <span class="material-icons">add_circle_outline</span>
	</a>
</h1>

<hr>

<?php
	$table = new \PHC\Components\Table;
	$table->resource = $this->employees;
	$table->columns = [
		'#' => 'id',
		'Name' => 'name',
		'E-mail' => 'email',
		'Admission Date' => [
			'admissionDate',
			[
				'method' => 'format',
				'args' => ['m/d/Y']
			]
		],
		'Supervisor' => ['supervisor', 'name'],
		'Roles' => function($row) {
			$roles = $row->roles ?? [];
			$names = array_map(function($item) {
				return $item->name;
			}, $roles);

			return implode(', ', $names);
		},
	];
	$table->actions = [
		(function () {
			$edit = new \PHC\Components\Form\Button;

			$edit->name = 'Edit';
			$edit->type = 'link';
			$edit->icon = 'edit';
			$edit->size = 's';
			$edit->style = 'success';
			$edit->action = '/employees/form/{row->id}';

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
			$delete->additional = ['data-destiny' => '/employees/delete/'];

			return $delete;
		})()
	];
	$modal->render();
?>
