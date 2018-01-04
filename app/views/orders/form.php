<h1><?=$pageTitle?></h1>

<hr>

<div class="component__products-pick-list"
	data-name="items-order"
	data-title="Items Order"
	data-value="id"
	data-label="name"
	data-source="/products/json">

	<spam class="material-icons loader">sync</spam>

	<div class="show-select-list"></div>
	<div class="show-selected-list">
		<table class="table table-bordered table-striped table-responsive table-hover">
			<thead class="thead-inverse">
				<tr>
					<th style="width: 5%; text-align: right;">#</th>
					<th>Items Order</th>
					<th style="width: 10%; text-align: right;">Quantity</th>
					<th style="width: 10%; text-align: right;">Price</th>
					<th style="width: 10%; text-align: right;">Subtotal</th>
					<th style="width: 5%;">Action</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>1</td>
					<td>Book 1</td>
					<td>2</td>
					<td>100.00</td>
					<td>200.00</td>
					<td>
						<a href="#" class="btn btn-sm btn-danger" data-value="1">
							<spam class="material-icons">delete</spam>
						</a>
					</td>
				</tr>
				<tr>
					<td>2</td>
					<td>Book 2</td>
					<td>3</td>
					<td>150.00</td>
					<td>450.00</td>
					<td>
						<a href="#" class="btn btn-sm btn-danger" data-value="2">
							<spam class="material-icons">delete</spam>
						</a>
					</td>
				</tr>
				<tr>
					<td>3</td>
					<td>Book 3</td>
					<td>4</td>
					<td>200.00</td>
					<td>800.00</td>
					<td>
						<a href="#" class="btn btn-sm btn-danger" data-value="4">
							<spam class="material-icons">delete</spam>
						</a>
					</td>
				</tr>
			</tbody>

			<tfoot class="text-white bg-dark">
				<tr class="font-weight-bold text-right">
					<td colspan="4">Total</td>
					<td colspan="2">1450.00</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<?php
	// vd($this->order ?? $this->order->finished ?? false);
	$this->form->action = '/orders';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => $this->order ?? $this->order->id
	]);

	$this->form->select([
		'name' => 'customer',
		'selected' => (
			!is_null($this->order) ?
			(
				!empty($this->order->customer) ?
				$this->order->customer->id :
				''
			) :
			''
		),
		'options' => $this->customers,
		'hideLabel' => true,
		'required' => true,
	]);

	$this->form->button([
		'name' => 'save',
		'style' => 'primary',
		'icon' => 'add_circle',
		'type' => 'submit',
	]);

	if (!is_null($this->order)) {
		$this->form->button([
			'name' => 'delete',
			'style' => 'danger',
			'icon' => 'delete',
			'additional' => [
				'data-toggle' => 'modal',
				'data-target' => '#confirm-modal'
			]
		]);
	}

	$this->form->button([
		'name' => 'cancel',
		'style' => 'warning',
		'icon' => 'cancel',
		'type' => 'link',
		'action' => '/orders'
	]);

	$this->form->render();
?>

<?php if (!is_null($this->order)) : ?>
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

					<form method="POST" id="confirm-form" action="/orders/delete/<?=$this->order->id?>">
						<button type="submit" class="btn btn-danger">
							Delete <span class="material-icons">delete</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
