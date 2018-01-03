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
					<th style="width: 5%;">Action</th>
				</tr>
			</thead>

			<tbody>
			</tbody>
		</table>
	</div>
</div>

<?php
	vd($this->order ?? $this->order->finished ?? false);
	$this->form->action = '/orders';
	$this->form->method = 'POST';

	$this->form->hidden([
		'name' => 'id',
		'value' => !is_null($this->order) ? $this->order->id : ''
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
		'width' => '1/2',
	]);

	$this->form->select([
		'name' => 'salesman',
		'selected' => (
			!is_null($this->order) ?
			(
				!empty($this->order->salesman) ?
				$this->order->salesman->id :
				''
			) :
			''
		),
		'options' => $this->employees,
		'hideLabel' => true,
		'required' => true,
		'width' => '1/2',
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
