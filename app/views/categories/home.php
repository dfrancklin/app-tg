<h1>
	<?=$pageTitle?>
	<a href="/categories/form" class="btn btn-primary">
		New <span class="material-icons">add_circle</span>
	</a>
</h1>

<hr>

<table class="component__table table table-bordered table-striped table-responsive table-hover">
	<thead class="thead-inverse">
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($categories as $category): ?>
			<tr>
				<th scope="row"><?=$category->id?></th>
				<td><?=$category->name?></td>
				<td>
					<a href="/categories/form/<?=$category->id?>" class="btn btn-sm btn-success">
						Edit <span class="material-icons">edit</span>
					</a>
					<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirm-modal" data-id="<?=$category->id?>">
						Delete <span class="material-icons">delete</span>
					</button>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php

$pagination = new \PHC\Components\Pagination;

$pagination->route = $this->router->getActiveRoute();
$pagination->active = $this->page;
$pagination->total = $this->totalPages;

$pagination->render();

$modal = new \PHC\Components\Modal;

$modal->name = 'confirm-modal';
$modal->title = 'Are you sure?';
$modal->body = '<p>Are you sure that you want to delete this item permanently?</p>';
$modal->actions = [(function () {
	$delete = new \PHC\Components\Form\Button;

	$delete->name = 'Delete';
	$delete->type = 'link';
	$delete->icon = 'delete';
	$delete->style = 'danger';
	$delete->additional = ['data-destiny' => '/categories/delete/'];

	return $delete;
})()];

$modal->render();
?>
