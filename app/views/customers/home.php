<h1>
	<?=$pageTitle?>
	<a href="/customers/form" class="btn btn-primary">
		New <span class="material-icons">add_circle</span>
	</a>
</h1>

<hr>

<table class="component__table table table-bordered table-striped table-responsive table-hover">
	<thead class="thead-inverse">
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($customers as $customer): ?>
			<tr>
				<th scope="row"><?=$customer->id?></th>
				<td><?=$customer->name?></td>
				<td><?=$customer->email?></td>
				<td><?=$customer->phone?></td>
				<td>
					<a href="/customers/form/<?=$customer->id?>" class="btn btn-sm btn-success">
						Edit <span class="material-icons">edit</span>
					</a>
					<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirm-modal" data-id="<?=$customer->id?>">
						Delete <span class="material-icons">delete</span>
					</button>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ($this->totalPages > 1) : ?>
	<nav aria-label="Page navigation">
		<ul class="pagination justify-content-center">
			<li class="page-item<?=($this->page === 1 ? ' disabled' : '')?>">
				<a class="page-link" href="/customers?page=<?=$this->page - 1?>" tabindex="-1">Previous</a>
			</li>

			<?php foreach (range(1, $this->totalPages) as $value) : ?>
				<li class="page-item<?=($this->page === $value ? ' active' : '')?>">
					<a class="page-link" href="/customers?page=<?=$value?>">
						<?=$value?>
						<?php if ($this->page === $value) : ?>
							<span class="sr-only">(current)</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>

			<li class="page-item<?=($this->page === $this->totalPages ? ' disabled' : '')?>">
				<a class="page-link" href="/customers?page=<?=$this->page + 1?>">Next</a>
			</li>
		</ul>
	</nav>
<?php endif; ?>

<div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-modal-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="confirm-modal-label">Are you sure?</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<p>Are you sure that you want to delete this item permanently?</p>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

				<form method="POST" id="confirm-form" data-destiny="/customers/delete/">
					<button type="submit" class="btn btn-danger">
						Delete <span class="material-icons">delete</span>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
