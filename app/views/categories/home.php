<h1>
	<?php echo $this->lang($pageTitle); ?>

	<a href="/categories/form" class="btn btn-primary">
		<?php echo $this->lang('new'); ?>
		<span class="material-icons">add_circle_outline</span>
	</a>
</h1>

<hr>

<?php
	$table = new \PHC\Components\Table;
	$table->resource = $this->categories;
	$table->columns = [
		'#' => 'id',
		$this->lang('name') => 'name',
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
			$edit->action = '/categories/form/{row->id}';

			return $edit;
		})(),
		(function () {
			$delete = new \PHC\Components\Form\Button;

			$delete->name = 'delete';
			$delete->icon = 'delete';
			$delete->title = $this->lang('edit');
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

			$delete->name = 'delete';
			$delete->type = 'link';
			$delete->title = $this->lang('delete');
			$delete->icon = 'delete';
			$delete->style = 'danger';
			$delete->additional = ['data-destiny' => '/categories/delete/'];

			return $delete;
		})()
	];
	$modal->render();
?>
