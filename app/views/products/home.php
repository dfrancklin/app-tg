<h1>
	<?=$pageTitle?>
	<a href="/products/form" class="btn btn-primary">
		New <span class="material-icons">add_circle</span>
	</a>
</h1>

<hr>

<form>
	<div class="component__picklist" data-source="/roles/json">
		<input type="text" class="form-control">
	</div>
</form>

<hr>

<table class="component__table table table-bordered table-striped table-responsive table-hover">
	<thead class="thead-default">
		<tr>
			<th>#</th>
			<th>Picture</th>
			<th>Name</th>
			<th>Price</th>
			<th>Quantity</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($products as $product): ?>
			<tr>
				<th scope="row"><?=$product->id?></th>
				<td class="image-cell">
					<?php if ($product->picture) : ?>
						<img src="<?=$product->picture?>" alt="<?=$product->name?>" class="img-fluid rounded">
					<?php endif; ?>
				</td>
				<td><?=$product->name?></td>
				<td>$ <?=number_format($product->price, 2)?></td>
				<td><?=$product->quantity?></td>
				<td>
					<a href="/products/form/<?=$product->id?>" class="btn btn-sm btn-success">
						Edit <span class="material-icons">edit</span>
					</a>
					<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirm-modal" data-id="<?=$product->id?>">
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
				<a class="page-link" href="/products?page=<?=$this->page - 1?>" tabindex="-1">Previous</a>
			</li>

			<?php foreach (range(1, $this->totalPages) as $value) : ?>
				<li class="page-item<?=($this->page === $value ? ' active' : '')?>">
					<a class="page-link" href="/products?page=<?=$value?>">
						<?=$value?>
						<?php if ($this->page === $value) : ?>
							<span class="sr-only">(current)</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>

			<li class="page-item<?=($this->page === $this->totalPages ? ' disabled' : '')?>">
				<a class="page-link" href="/products?page=<?=$this->page + 1?>">Next</a>
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

				<form method="POST" id="confirm-form">
					<button type="submit" class="btn btn-danger">
						Delete <span class="material-icons">delete</span>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
