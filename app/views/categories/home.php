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
$pagination = new \PHC\Components\PaginationComponent;

$pagination->route = $this->router->getActiveRoute();
$pagination->active = $this->page;
$pagination->total = $this->totalPages;

$pagination->render();

if ($this->totalPages > 1) : ?>
	<nav aria-label="Page navigation">
		<ul class="pagination justify-content-center">
			<li class="page-item<?=($this->page === 1 ? ' disabled' : '')?>">
				<a class="page-link" href="/categories?page=<?=$this->page - 1?>" tabindex="-1">Previous</a>
			</li>

			<?php foreach (range(1, $this->totalPages) as $value) : ?>
				<li class="page-item<?=($this->page === $value ? ' active' : '')?>">
					<a class="page-link" href="/categories?page=<?=$value?>">
						<?=$value?>
						<?php if ($this->page === $value) : ?>
							<span class="sr-only">(current)</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>

			<li class="page-item<?=($this->page === $this->totalPages ? ' disabled' : '')?>">
				<a class="page-link" href="/categories?page=<?=$this->page + 1?>">Next</a>
			</li>
		</ul>
	</nav>
<?php
	endif;


	$modal = new \PHC\Components\ModalComponent;

	$modal->name = 'confirm-modal';
	$modal->title = 'Are you sure?';
	$modal->body = '<p>Are you sure that you want to delete this item permanently?</p>';
	$modal->actions = [(function () {
		$delete = new \PHC\Components\Form\ButtonComponent;

		$delete->name = 'Delete';
		$delete->type = 'link';
		$delete->icon = 'delete';
		$delete->style = 'danger';
		$delete->additional = ['data-destiny' => '/categories/delete/'];

		return $delete;
	})()];

	$modal->render();
?>
