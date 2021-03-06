<h1>
	<?php echo $this->lang($pageTitle); ?>

	<a href="/employees/form" class="btn btn-primary">
		<?php echo $this->lang('new'); ?>
		<span class="material-icons">add_circle_outline</span>
	</a>
</h1>

<hr>

<?php
	$table = new \PHC\Components\Table;
	$table->resource = $this->employees;
	$table->columns = [
		'#' => 'id',
		$this->lang('name') => 'name',
		$this->lang('email') => 'email',
		$this->lang('admission-date') => [ 'admissionDate', [ 'method' => 'format', 'args' => [ DATE_FORMAT ] ] ],
		$this->lang('supervisor') => ['supervisor', 'name'],
		$this->lang('roles') => function($row) {
			$roles = $row->roles ?? [];
			$names = array_map(function($item) {
				return $item->name;
			}, $roles);

			return implode(', ', $names);
		},
	];
	$table->actionsLabel = $this->lang('actions');
	$table->actions = [
		(function () {
			$edit = new \PHC\Components\Form\Button;

			$edit->name = 'edit';
			$edit->type = 'link';
			$edit->title = $this->lang('edit');
			$edit->icon = 'edit';
			$edit->size = 's';
			$edit->style = 'success';
			$edit->action = '/employees/form/{row->id}';

			return $edit;
		})(),
		(function () {
			$delete = new \PHC\Components\Form\Button;

			$delete->name = 'delete';
			$delete->icon = 'delete';
			$delete->title = $this->lang('delete');
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
	$pagination->firstLabel = $this->lang('first');
	$pagination->nextLabel = $this->lang('next');
	$pagination->previousLabel = $this->lang('previous');
	$pagination->lastLabel = $this->lang('last');
	$pagination->render();

	$modal = new \PHC\Components\Modal;
	$modal->name = 'confirm-modal';
	$modal->title = $this->lang('confirm-modal-title');
	$modal->body = $this->lang('confirm-modal-message');
	$modal->closeButtonLabel = $this->lang('close');
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
